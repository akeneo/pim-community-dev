<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;

class InMemoryFindRequiredValueKeyCollectionForChannelAndLocales implements FindRequiredValueKeyCollectionForChannelAndLocalesInterface
{
    private InMemoryAttributeRepository $attributeRepository;

    /** @var string[] */
    private ?array $activatedChannelCodes = null;

    /** @var string[] */
    private ?array $activatedLocaleCodes = null;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function find(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifierCollection $localeIdentifierCollection
    ): ValueKeyCollection {
        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);
        $valueKeys = [];

        $channelCode = $channelIdentifier->normalize();
        $localeCodes = $localeIdentifierCollection->normalize();

        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (false === $attribute->normalize()['is_required']) {
                continue;
            }

            if ($attribute->hasValuePerChannel() && $attribute->hasValuePerLocale()) {
                foreach ($this->activatedChannelCodes as $activatedChannelCode) {
                    foreach ($localeCodes as $localeCode) {
                        if ($activatedChannelCode === $channelCode && in_array($localeCode, $this->activatedLocaleCodes)) {
                            $valueKeys[] = sprintf(
                                '%s_%s_%s',
                                (string) $attribute->getIdentifier(),
                                $activatedChannelCode,
                                $localeCode
                            );
                        }
                    }
                }
            } elseif ($attribute->hasValuePerChannel()) {
                foreach ($this->activatedChannelCodes as $activatedChannelCode) {
                    if ($activatedChannelCode === $channelCode) {
                        $valueKeys[] = sprintf(
                            '%s_%s',
                            (string) $attribute->getIdentifier(),
                            $activatedChannelCode
                        );
                    }
                }
            } elseif ($attribute->hasValuePerLocale()) {
                foreach ($localeCodes as $localeCode) {
                    if (in_array($localeCode, $this->activatedLocaleCodes)) {
                        $valueKeys[] = sprintf(
                            '%s_%s',
                            (string) $attribute->getIdentifier(),
                            $localeCode
                        );
                    }
                }
            } else {
                $valueKeys[] = (string) $attribute->getIdentifier();
            }
        }

        $valueKeys = array_map(fn($key) => ValueKey::createFromNormalized($key), $valueKeys);

        return ValueKeyCollection::fromValueKeys($valueKeys);
    }

    public function setActivatedChannels(array $channelCodes): void
    {
        $this->activatedChannelCodes = $channelCodes;
    }

    public function setActivatedLocales(array $localeCodes): void
    {
        $this->activatedLocaleCodes = $localeCodes;
    }
}
