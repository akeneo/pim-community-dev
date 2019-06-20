<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateOptionCollectionAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionAttributeFactory implements AttributeFactoryInterface
{
    public function supports(AbstractCreateAttributeCommand $command): bool
    {
        return $command instanceof CreateOptionCollectionAttributeCommand;
    }

    public function create(
        AbstractCreateAttributeCommand $command,
        AttributeIdentifier $identifier,
        AttributeOrder $order
    ): AbstractAttribute {
        if (!$this->supports($command)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected command of type "%s", "%s" given',
                    CreateOptionCollectionAttributeCommand::class,
                    get_class($command)
                )
            );
        }

        return OptionCollectionAttribute::create(
            $identifier,
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            AttributeCode::fromString($command->code),
            LabelCollection::fromArray($command->labels),
            $order,
            AttributeIsRequired::fromBoolean($command->isRequired),
            AttributeValuePerChannel::fromBoolean($command->valuePerChannel),
            AttributeValuePerLocale::fromBoolean($command->valuePerLocale)
        );
    }
}
