<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EmptyValueCommand;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformationsSource;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * Empty the value of asset
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EmptyUpdater implements ValueUpdaterInterface
{
    private GetTransformationsSource $getTransformationsSource;
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(
        GetTransformationsSource $getTransformationsSource,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->getTransformationsSource = $getTransformationsSource;
        $this->attributeRepository = $attributeRepository;
    }

    public function supports(AbstractEditValueCommand $command): bool
    {
        return $command instanceof EmptyValueCommand;
    }

    public function __invoke(Asset $asset, AbstractEditValueCommand $command): void
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException('Impossible to update the value of the asset with the given command.');
        }

        $attribute = $command->attribute;
        $attributeIdentifier = $attribute->getIdentifier();
        $channelReference = (null !== $command->channel) ?
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($command->channel)) :
            ChannelReference::noReference();
        $localeReference = (null !== $command->locale) ?
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($command->locale)) :
            LocaleReference::noReference();
        $emptyData = EmptyData::create();

        $value = Value::create($attributeIdentifier, $channelReference, $localeReference, $emptyData);
        $asset->setValue($value);

        $this->emptyAttributesTargetedByAttribute($asset, $attribute, $channelReference, $localeReference);
    }

    private function emptyAttributesTargetedByAttribute(
        Asset $asset,
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): void {
        $transformations = $this->getTransformationsSourceForAttribute(
            $attribute,
            $channelReference,
            $localeReference
        );

        foreach ($transformations as $transformation) {
            $command = $this->createEmptyValueCommand($asset, $transformation->getTarget());
            if (! $command instanceof EmptyValueCommand) {
                continue;
            }

            $this->__invoke($asset, $command);
        }
    }

    private function createEmptyValueCommand(Asset $asset, Target $target): ?EmptyValueCommand
    {
        try {
            $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
                $target->getAttributeCode(),
                $asset->getAssetFamilyIdentifier()
            );

            return new EmptyValueCommand(
                $attribute,
                $target->getChannelReference()->normalize(),
                $target->getLocaleReference()->normalize()
            );
        } catch (AttributeNotFoundException $exception) {
            return null;
        }
    }

    private function getTransformationsSourceForAttribute(
        AbstractAttribute $attribute,
        ChannelReference $channelReference,
        LocaleReference $localeReference
    ): array {
        return $this->getTransformationsSource->forAttribute(
            $attribute,
            $channelReference,
            $localeReference
        );
    }
}
