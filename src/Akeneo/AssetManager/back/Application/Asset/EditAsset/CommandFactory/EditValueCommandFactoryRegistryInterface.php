<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;

interface EditValueCommandFactoryRegistryInterface
{
    public function register(EditValueCommandFactoryInterface $editDataCommandFactory): void;

    public function getFactory(AbstractAttribute $attribute, array $normalizedValue): EditValueCommandFactoryInterface;
}
