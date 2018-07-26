<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;

interface CreateAttributeCommandFactoryInterface
{
    public function supports(array $normalizedCommand): bool;

    public function create(array $normalizedCommand): AbstractCreateAttributeCommand;
}
