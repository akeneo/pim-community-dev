<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

interface FindValueKeysToIndexForAllChannelsAndLocalesInterface
{
    /**
     * Returns all the value keys for all channels and locales
     * [
     *   'ecommerce' => [
     *      'fr_FR' => ['vk1', 'vk2'],
     *      'en_US' => ['vkx', 'vky']
     *   ],
     *   'mobile' => [ ... ]
     * ]
     *
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     *
     * @return array
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array;
}
