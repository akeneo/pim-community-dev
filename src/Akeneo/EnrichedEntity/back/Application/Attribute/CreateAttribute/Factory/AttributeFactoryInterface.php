<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Factory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\Command\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface AttributeFactoryInterface
{
    public function supports(AbstractCreateAttributeCommand $command): bool;

    public function create(AbstractCreateAttributeCommand $command): AbstractAttribute;
}
