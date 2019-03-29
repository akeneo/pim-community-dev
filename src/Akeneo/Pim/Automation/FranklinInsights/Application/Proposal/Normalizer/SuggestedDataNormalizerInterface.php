<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface SuggestedDataNormalizerInterface
{
    /**
     * Returns suggested values in Akeneo PIM standard format.
     *
     * We first get the attribute types for each of the attributes of the suggested values.
     * The attribute types list is formatted as follow:
     *    [
     *        'attribute_code' => 'attribute_type',
     *    ]
     * If a suggested value refers to an attribute that does not exist, it will not be present in this list.
     *
     * @param SuggestedData $suggestedData
     *
     * @return array
     */
    public function normalize(SuggestedData $suggestedData): array;
}
