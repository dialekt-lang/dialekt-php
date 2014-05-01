<?php
namespace Icecave\Dialekt\Parser;

final class Token
{
    const LOGICAL_AND = 1;
    const LOGICAL_OR  = 2;
    const LOGICAL_NOT = 3;
    const STRING      = 4;
    const OPEN_NEST   = 6;
    const CLOSE_NEST  = 7;

    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public $type;
    public $value;
}
