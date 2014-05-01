<?php
namespace Icecave\Dialekt\Parser;

final class Token
{
    const TOKEN_LOGICAL_AND = 1;
    const TOKEN_LOGICAL_OR  = 2;
    const TOKEN_LOGICAL_NOT = 3;
    const TOKEN_STRING      = 4;
    const TOKEN_OPEN_NEST   = 6;
    const TOKEN_CLOSE_NEST  = 7;

    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public $type;
    public $value;
}
