<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface EditRecordValueCommandFactoryRegistryInterface
{
    public function register(EditRecordValueCommandFactoryInterface $editRecordValueCommandFactory): void;

    public function getFactory(AbstractAttribute $attribute): EditRecordValueCommandFactoryInterface;
}
