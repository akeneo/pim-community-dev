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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Resolve path for a proposal attribute.
 */
class ProposalAttributePathResolver
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Return attribute path for elasticsearch query
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    public function getAttributePaths(AttributeInterface $attribute): array
    {
        $baseAttribute = 'values.' . $attribute->getCode() . '-' . $attribute->getBackendType();

        if ($attribute->isScopable()) {
            return $this->getPathsForScopableAttribute($baseAttribute, $attribute);
        }

        if ($attribute->isLocaleSpecific()) {
            return $this->getPathsForLocalizableAttribute($attribute->getAvailableLocaleCodes(), '<all_channels>', $baseAttribute);
        }

        if ($attribute->isLocalizable()) {
            return $this->getPathsForLocalizableAttribute($this->localeRepository->getActivatedLocaleCodes(), '<all_channels>', $baseAttribute);
        }

        return [$baseAttribute . '.<all_channels>.<all_locales>'];
    }

    /**
     * Get the paths for a scopable attribute.
     * Can return 'values.text-textarea.ecommmerce.fr_FR' if attribute is localizable or with specific locale
     * or 'values.text-textarea.ecommmerce.<all_locales>' if attribute is not localizable.
     *
     * @param string             $baseAttribute
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    private function getPathsForScopableAttribute(string $baseAttribute, AttributeInterface $attribute): array
    {
        $paths = [];

        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $localeCodes = ['<all_locales>'];

            if ($attribute->isLocaleSpecific()) {
                $localeCodes = $attribute->getAvailableLocaleCodes();
            } elseif (!$attribute->isLocalizable()) {
                $localeCodes = ['<all_locales>'];
            } elseif ($channel->getLocaleCodes()) {
                $localeCodes = $channel->getLocaleCodes();
            }

            $paths = array_merge(
                $paths,
                $this->getPathsForLocalizableAttribute($localeCodes, $channel->getCode(), $baseAttribute)
            );
        }

        return $paths;
    }

    /**
     * Get the paths for a localizable attribute.
     * Can return 'values.text-textarea.ecommmerce.fr_FR' if attribute is localizable or with specific locale
     * or 'values.text-textarea.<all_channels>.fr_FR' if attribute is not scopable.
     *
     * @param array  $localeCodes
     * @param string $channelCode
     * @param string $baseAttribute
     *
     * @return array
     */
    private function getPathsForLocalizableAttribute(array $localeCodes, string $channelCode, string $baseAttribute): array
    {
        return array_map(function ($localeCode) use ($baseAttribute, $channelCode) {
            return sprintf('%s.%s.%s', $baseAttribute, $channelCode, $localeCode);
        }, $localeCodes);
    }
}
