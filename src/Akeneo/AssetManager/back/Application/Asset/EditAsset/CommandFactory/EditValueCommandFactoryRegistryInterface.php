<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

interface EditValueCommandFactoryRegistryInterface
{
    public function register(EditValueCommandFactoryInterface $editDataCommandFactory): void;

    public function getFactory(AbstractAttribute $attribute, array $normalizedValue): EditValueCommandFactoryInterface;
}
