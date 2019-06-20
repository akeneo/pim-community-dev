<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Attribute;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

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
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     *
     * @return array
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array;
}
