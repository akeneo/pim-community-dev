<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

interface EditRecordValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $attribute): bool;

    public function create(array $normalizedCommand, AbstractAttribute $attribute);
}
