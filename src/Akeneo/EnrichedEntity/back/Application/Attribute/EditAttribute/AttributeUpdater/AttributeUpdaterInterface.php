<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool;

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute;
}
