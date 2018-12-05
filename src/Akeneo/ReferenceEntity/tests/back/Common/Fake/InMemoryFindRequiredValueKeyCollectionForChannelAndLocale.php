<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindRequiredValueKeyCollectionForChannelAndLocaleInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;

class InMemoryFindRequiredValueKeyCollectionForChannelAndLocale implements FindRequiredValueKeyCollectionForChannelAndLocaleInterface
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
        LocaleIdentifier $localeIdentifier
    ): ValueKeyCollection {
        $attributes = $this->attributeRepository->findByReferenceEntity($referenceEntityIdentifier);
        $valueKeys = [];

        $channelCode = $channelIdentifier->normalize();
        $localeCode = $localeIdentifier->normalize();

        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->hasValuePerChannel() && $attribute->hasValuePerLocale()) {
                foreach ($this->activatedChannelCodes as $activatedChannelCode) {
                    foreach ($this->activatedLocaleCodes as $activatedLocaleCode) {
                        if ($activatedChannelCode === $channelCode && $activatedLocaleCode === $localeCode) {
                            $valueKeys[] = sprintf(
                                '%s_%s_%s',
                                (string) $attribute->getIdentifier(),
                                $activatedChannelCode,
                                $activatedLocaleCode
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
                foreach ($this->activatedLocaleCodes as $activatedLocaleCode) {
                    if ($activatedLocaleCode === $localeCode) {
                        $valueKeys[] = sprintf(
                            '%s_%s',
                            (string) $attribute->getIdentifier(),
                            $activatedLocaleCode
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
