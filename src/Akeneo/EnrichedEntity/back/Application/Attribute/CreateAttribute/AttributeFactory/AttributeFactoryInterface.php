<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface AttributeFactoryInterface
{
    public function supports(AbstractCreateAttributeCommand $command): bool;

    public function create(AbstractCreateAttributeCommand $command): AbstractAttribute;
}
