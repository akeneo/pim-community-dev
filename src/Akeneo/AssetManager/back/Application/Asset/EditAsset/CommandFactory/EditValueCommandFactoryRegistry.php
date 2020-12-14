<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditValueCommandFactoryRegistry implements EditValueCommandFactoryRegistryInterface
{
    private $commandFactories = [];

    public function register(EditValueCommandFactoryInterface $editDataCommandFactory): void
    {
        $this->commandFactories[] = $editDataCommandFactory;
    }

    public function getFactory(AbstractAttribute $attribute, array $normalizedValue): EditValueCommandFactoryInterface
    {
        foreach ($this->commandFactories as $commandFactory) {
            if ($commandFactory->supports($attribute, $normalizedValue)) {
                return $commandFactory;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no factory found to create the edit asset value command of the attribute "%s"',
                $attribute->getIdentifier()->normalize()
            )
        );
    }
}
