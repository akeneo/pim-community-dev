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
 * Classes that implement this interface should return a normalized value.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface StructuredAttributeRepositoryInterface
{
    /**
     * It has a structured format of attributes code and their attribute group code corresponding.
     *
     * @param string $identifier
     * @param string $scope
     * @param string $locale
     *
     * @return array
     */
    public function getStructuredAttributes($identifier, $scope, $locale);
}
