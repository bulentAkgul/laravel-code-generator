<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;

class SetKeys
{
    public static function _(array $attr, array $map): array
    {
        return self::combine(match ($attr['relation']) {
            'mtm' => self::setKeysForMTM($attr, $map),
            default => self::setKeysForOTX($attr, $map)
        });
    }

    private static function setKeysForOTX($attr, $map): array
    {
        return match(true) {
            $attr['polymorphic'] => self::setKeysForPolymorphicOTX($attr, $map),
            $attr['is_through'] => self::setKeysForThroughOTX($attr, $map),
            default => self::setKeysForDefaultOTX($attr)
        };
    }

    private static function setKeysForPolymorphicOTX($attr, $map): array
    {
        return [];
    }

    private static function setKeysForThroughOTX($attr, $map): array
    {
        return [
            'mediator_id' => self::append($m = $attr['mediator_t_key'] != 'id' ? $attr['mediator_t_key'] : ''),
            'from_id' => self::append($f = $attr['from_key'] != 'id' ? $attr['from_key'] : ($m ? 'id' : '')),
            'to_key' => $t = self::setPrefixedKey($attr, 'to', 'mediator_t', $f),
            'mediator_key' => self::setPrefixedKey($attr, 'mediator_f', 'from', $t),
        ];
    }

    private static function setKeysForDefaultOTX($attr): array
    {
        return [
            'from_key' => $f = self::setUnprefixedKey($attr, 'from'),
            'to_key' => self::setPrefixedKey($attr, 'to', 'from', $f),
        ];
    }

    private static function setKeysForMTM($attr, $map): array
    {
        return match(true) {
            $attr['polymorphic'] => self::setKeysForPolymorphicMTM($attr, $map),
            default => self::setKeysForDefaultMTM($attr, $map)
        };
    }

    private static function setKeysForPolymorphicMTM($attr, $map): array
    {
        return [];
    }

    private static function setKeysForDefaultMTM($attr, $map): array
    {
        return [];
    }

    private static function isNotKeyable(string $key, ?string $after = null): bool
    {
        return $key == 'id' && !$after;
    }

    private static function setPrefixedKey(array $attr, string $key, string $pair, string $after): string
    {
        if (self::isNotKeyable($attr["{$key}_key"], $after)) return '';
    
        return self::append(implode('_', [
            self::setPrefix($attr, explode('_', $pair)[0]),
            self::setKey($attr, $key, $pair)
        ]));
    }

    private static function setUnprefixedKey(array $attr, string $key, string $after = ''): string
    {
        return self::isNotKeyable($attr["{$key}_key"], $after) ? '' : self::append($attr["{$key}_key"]);
    }

    private static function setPrefix(array $attr, string $pair): string
    {
        return ConvertCase::snake($attr["{$pair}_table"], true);
    }

    private static function setKey(array $attr, string $key, string $pair)
    {
        return $attr["{$key}_key"] != 'id' ? $attr["{$key}_key"] : $attr["{$pair}_key"];
    }

    private static function append(string $str): string
    {
        return Text::append(Text::wrap($str, 'sq'), ', ');
    }

    private static function combine(array $keys): array
    {
        return [...self::keys(), ...$keys];
    }

    private static function keys(): array
    {
        return Arry::combine(
            ['from_key', 'from_id', 'to_key', 'mediator_key', 'mediator_id'],
            default: ''
        );
    }
}