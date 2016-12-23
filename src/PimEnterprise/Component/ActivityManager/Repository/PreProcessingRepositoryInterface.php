<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Repository;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface PreProcessingRepositoryInterface
{
    /**
     * TODO: merge projectCompletenessRepository ?
     *
     * Inserts data into the pre processing table.
     *
     * @param string $productId
     * @param string $channelId
     * @param string $localeId
     * @param array $attributeGroupCompleteness
     * @return
     * @internal param string $attributeGroupId
     * @internal param bool $atLeast
     * @internal param bool $complete
     */
    public function save($productId, $channelId, $localeId, array $attributeGroupCompleteness);
}
