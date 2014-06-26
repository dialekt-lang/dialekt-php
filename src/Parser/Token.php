<?php
namespace Icecave\Dialekt\Parser;

use LogicException;

final class Token
{
    const WILDCARD_CHARACTER = '*';

    const LOGICAL_AND   = 1;
    const LOGICAL_OR    = 2;
    const LOGICAL_NOT   = 3;
    const STRING        = 4;
    const OPEN_BRACKET  = 6;
    const CLOSE_BRACKET = 7;

    public function __construct(
        $type,
        $value,
        $offset,
        $length,
        $line,
        $column
    ) {
        $this->type = $type;
        $this->value = $value;
        $this->offset = $offset;
        $this->length = $length;
        $this->line = $line;
        $this->column = $column;
    }

    public static function typeDescription($type)
    {
        switch ($type) {
            case self::LOGICAL_AND:
                return 'AND operator';
            case self::LOGICAL_OR:
                return 'OR operator';
            case self::LOGICAL_NOT:
                return 'NOT operator';
            case self::STRING:
                return 'tag';
            case self::OPEN_BRACKET:
                return 'open bracket';
            case self::CLOSE_BRACKET:
                return 'close bracket';
        };

        throw new LogicException('Unknown type.');
    }

    public $type;
    public $value;
    public $offset;
    public $length;
    public $line;
    public $column;
}
