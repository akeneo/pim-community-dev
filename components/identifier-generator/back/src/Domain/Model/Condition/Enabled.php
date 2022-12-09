<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

class Enabled implements ConditionInterface
{
    public static function type(): string
    {
        return 'enabled';
    }
}
