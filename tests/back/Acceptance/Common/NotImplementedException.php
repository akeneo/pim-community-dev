<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Common;

/**
 * Use this exception when you don't need all methods from an interface.
 *
 * Example: when you create a in memory repository, your class needs to implement ObjectRepository interface
 * but you just need the method findBy from this interface.
 *
 */
final class NotImplementedException extends \Exception
{
    public function __construct(string $method)
    {
        parent::__construct(sprintf('The method %s is not implemented yet', $method));
    }
}
