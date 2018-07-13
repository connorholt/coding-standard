<?php declare(strict_types = 1);

namespace SlevomatCodingStandard\Helpers;

use Generator;
use PHP_CodeSniffer\Files\File;
use const T_STRING;
use function array_map;
use function iterator_to_array;
use function sprintf;

class ClassHelper
{

	public static function getFullyQualifiedName(File $codeSnifferFile, int $classPointer): string
	{
		$name = sprintf('%s%s', NamespaceHelper::NAMESPACE_SEPARATOR, self::getName($codeSnifferFile, $classPointer));
		$namespace = NamespaceHelper::findCurrentNamespaceName($codeSnifferFile, $classPointer);
		return $namespace !== null ? sprintf('%s%s%s', NamespaceHelper::NAMESPACE_SEPARATOR, $namespace, $name) : $name;
	}

	public static function getName(File $codeSnifferFile, int $classPointer): string
	{
		$tokens = $codeSnifferFile->getTokens();
		return $tokens[TokenHelper::findNext($codeSnifferFile, T_STRING, $classPointer + 1, $tokens[$classPointer]['scope_opener'])]['content'];
	}

	/**
	 * @param \PHP_CodeSniffer\Files\File $codeSnifferFile
	 * @return string[]
	 */
	public static function getAllNames(File $codeSnifferFile): array
	{
		$previousClassPointer = 0;

		return array_map(
			function (int $classPointer) use ($codeSnifferFile): string {
				return self::getName($codeSnifferFile, $classPointer);
			},
			iterator_to_array(self::getAllClassPointers($codeSnifferFile, $previousClassPointer))
		);
	}

	private static function getAllClassPointers(File $codeSnifferFile, int &$previousClassPointer): Generator
	{
		do {
			$nextClassPointer = TokenHelper::findNext($codeSnifferFile, TokenHelper::$typeKeywordTokenCodes, $previousClassPointer + 1);
			if ($nextClassPointer === null) {
				break;
			}

			$previousClassPointer = $nextClassPointer;
			yield $nextClassPointer;
		} while (true);
	}

}
