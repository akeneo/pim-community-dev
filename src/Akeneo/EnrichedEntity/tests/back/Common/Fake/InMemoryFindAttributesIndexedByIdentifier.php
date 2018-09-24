<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindAttributesIndexedByIdentifier implements FindAttributesIndexedByIdentifierInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return AbstractAttributeDetails[]
     */
    public function __invoke(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $attributes = $this->attributeRepository->findByEnrichedEntity($enrichedEntityIdentifier);

        return array_reduce($attributes, function ($stack, AbstractAttribute $current) {
            $stack[(string) $current->getIdentifier()] = $current;

            return $stack;
        }, []);

    }
//    /** @var InMemoryAttributeRepository */
//    private $attributeRepository;
//
//    /** @var InMemoryChannelRepository */
//    private $channelRepository;
//
//    /** @var InMemoryLocaleRepository */
//    private $localeRepository;
//
//    public function __construct(
//        InMemoryAttributeRepository $attributeRepository,
//        InMemoryChannelRepository $channelRepository,
//        InMemoryLocaleRepository $localeRepository
//    ) {
//        $this->attributeRepository = $attributeRepository;
//        $this->channelRepository = $channelRepository;
//        $this->localeRepository = $localeRepository;
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function __invoke(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
//    {
//        $attributes = $this->attributeRepository->findByEnrichedEntity($enrichedEntityIdentifier);
//
//        return $this->generateValueKeys($attributes);
//    }
//
//    /**
//     * The array returned looks like this:
//     * [
//     *     'normalized_value_key' => Attribute object,
//     * ]
//     *
//     * @param AbstractAttribute[] $attributes
//     *
//     * @return
//     */
//    private function generateValueKeys(array $attributes): array
//    {
//        $readModel = [];
//        foreach ($attributes as $attribute) {
//            $hasValuePerChannel = $attribute->hasValuePerChannel();
//            $hasValuePerLocale = $attribute->hasValuePerLocale();
//
//            if ($hasValuePerChannel && $hasValuePerLocale) {
//                /** @var Channel $channel */
//                foreach ($this->channelRepository->findAll() as $channel) {
//                    foreach ($channel->getLocaleCodes() as $localeCode) {
//                        $valueKey = ValueKey::create(
//                            $attribute->getIdentifier(),
//                            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($channel->getCode())),
//                            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($localeCode))
//                        );
//                        $readModel[(string) $valueKey] = $attribute;
//                    }
//                }
//
//                continue;
//            }
//
//            if ($hasValuePerChannel) {
//                /** @var Channel $channel */
//                foreach ($this->channelRepository->findAll() as $channel) {
//                    $valueKey = ValueKey::create(
//                        $attribute->getIdentifier(),
//                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode($channel->getCode())),
//                        LocaleReference::noReference()
//                    );
//                    $readModel[(string) $valueKey] = $attribute;
//                }
//
//                continue;
//            }
//
//            if ($hasValuePerLocale) {
//                /** @var Channel $channel */
//                foreach ($this->channelRepository->findAll() as $channel) {
//                    $valueKey = ValueKey::create(
//                        $attribute->getIdentifier(),
//                        ChannelReference::noReference(),
//                        LocaleReference::
//                    );
//                    $readModel[(string) $valueKey] = $attribute;
//                }
//            }
//        }
//
//        return $readModel;
//    }
}
