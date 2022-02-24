<?php

namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Service;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\AttributesBag;
use CG\Proxy\MethodInvocation;

trait PHPAttributeAware
{
    private ?array $attributes=null;

    protected function initAttributes(MethodInvocation $invocation, string $class): void
    {
        if (is_null($this->attributes)) {//TODO this might be cached.
            $this->attributes = $invocation
                ->reflection->getAttributes($class);
        }
    }
}
