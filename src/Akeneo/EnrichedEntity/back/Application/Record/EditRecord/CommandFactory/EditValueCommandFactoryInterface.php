<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;

interface EditValueCommandFactoryInterface
{
    public function supports(AbstractAttribute $attribute): bool;

    public function create(AbstractAttribute $attribute, $normalizedCommand);
}
