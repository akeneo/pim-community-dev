<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

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

    public function getFactory(AbstractAttribute $attribute): EditValueCommandFactoryInterface
    {
        foreach ($this->commandFactories as $commandFactory) {
            if ($commandFactory->supports($attribute)) {
                return $commandFactory;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no factory found to create the edit record value command of the attribute "%s"',
                $attribute->getIdentifier()->normalize()
            )
        );
    }
}
