<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Completeness;

/**
 * Compares and generate the pre-processing data.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface PreProcessingGeneratorInterface
{
    /**
     * Should generate and save the pre-processing data.
     *
     * @param int    $productId
     * @param string $scope
     * @param string $locale
     *
     * @return array
     */
    public function generate($productId, $scope, $locale);
}
