<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface FamilyRequirementRepositoryInterface
{
    /**
     * Find attribute group identifiers which have at least one attribute required
     * by a family and a channel.
     *
     * @param FamilyInterface  $family
     * @param ChannelInterface $channel
     *
     * @return string[]
     */
    public function findAttributeGroupIdentifiers(FamilyInterface $family, ChannelInterface $channel);

    /**
     * Return the attribute codes required by the product family depending on the project channel.
     * Those attributes are indexed by attribute group ids.
     *
     * [
     *      40 => [
     *          'sku',
     *          'name',
     *      ],
     *      33 => [
     *          'description',
     *      ],
     * ];
     *
     * @param ProductInterface $product
     * @param ChannelInterface $channel
     * @param LocaleInterface $locale
     *
     * @return array
     */
    public function findRequiredAttributes(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    );
}
