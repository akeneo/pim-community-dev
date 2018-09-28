<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface EditValueCommandFactoryRegistryInterface
{
    public function register(EditValueCommandFactoryInterface $editDataCommandFactory): void;

    public function getFactory(AbstractAttribute $attribute): EditValueCommandFactoryInterface;
}
