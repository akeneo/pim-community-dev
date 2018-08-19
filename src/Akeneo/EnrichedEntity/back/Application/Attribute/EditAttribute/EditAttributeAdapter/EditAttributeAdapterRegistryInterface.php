<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\EditAttributeAdapter;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;

/** /!\ Naming for this class is this really an adapteur: command -> adapteur -> entity update call */
interface EditAttributeAdapterRegistryInterface
{
    public function register(EditAttributeAdapterInterface $editAttributeAdapter): void;

    public function getAdapter(AbstractEditAttributeCommand $command): EditAttributeAdapterInterface;
}
