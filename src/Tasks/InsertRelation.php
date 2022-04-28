<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\FileContent\Helpers\Content;
use Bakgul\FileContent\Tasks\MutateIndentation;
use Bakgul\FileContent\Tasks\WriteToFile;
use Bakgul\Kernel\Helpers\Text;

class InsertRelation
{
    protected static $fileContent;

    public static function _(array $request, string|array $code)
    {
        self::$fileContent = file($request['attr']['target_file']);

        [$start, $indentation] = self::getSpecs();

        WriteToFile::handle(
            self::insertCode($code, $indentation, $start),
            $request['attr']['target_file']
        );
    }

    private static function getSpecs(): array
    {
        $count = count(self::$fileContent);

        foreach (array_reverse(self::$fileContent) as $i => $line) {
            if (self::endOfFile($line)) return [$s = $count - $i - 1, self::getIndentation($s)];
        }
    }

    private static function getIndentation(int $start): string
    {
        $j = 1;
        $indentation = '';

        while ($indentation == '') {
            $line = self::$fileContent[$start - $j];
            $indentation = trim($line) != '' ? MutateIndentation::get($line) : '';
            $j++;
        }

        return $indentation;
    }

    private static function endOfFile($line)
    {
        return trim($line) == '}';
    }

    private static function insertCode($code, $indentation, $start)
    {
        return Content::regenerate(self::$fileContent, $start, self::prepareCode($code, $indentation));
    }

    private static function prepareCode(string|array $code, string $indentation): array
    {
        return array_merge([PHP_EOL], array_map(
            fn ($x) => $indentation . $x,
            is_array($code) ? $code : Text::split($code)
        ));
    }
}