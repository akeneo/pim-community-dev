<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;

class InMemoryFindRequiredValueKeyCollectionForChannelAndLocales implements FindRequiredValueKeyCollectionForChannelAndLocalesInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var string[] */
    private $activatedChannelCodes;

    /** @var string[] */
    private $activatedLocaleCodes;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ChannelIdentifier $channelIdentifier,
        LocaleIdentifierCollection $localeIdentifierCollection
    ): ValueKeyCollection {
        $attributes = $this->attributeRepository->findByReferenceEntity($referenceEntityIdentifier);
        $valueKeys = [];

        $channelCode = $channelIdentifier->normalize();
        $localeCodes = $localeIdentifierCollection->normalize();

        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
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

        $valueKeys = array_map(function ($key) {
            return ValueKey::createFromNormalized($key);
        }, $valueKeys);

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
