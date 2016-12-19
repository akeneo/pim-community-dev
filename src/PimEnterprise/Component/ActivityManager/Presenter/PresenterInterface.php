<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Presenter;

/**
 * TODO: maybe move into completeness folder or somewhere outside the activity manager bundle ?
 *
 * Converts values from different incoming sources into a normalized data structure in order to be compared.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface PresenterInterface
{
    /**
     * Should converts incoming values into normalized values like that:
     *
     * $mandatoryAttributes = [
     *      'marketing' => [
     *          'sku',
     *          'name',
     *      ],
     * ];
     *
     * @param array $values
     * @param array $options
     *
     * @return array
     */
    public function present(array $values, array $options);
}
