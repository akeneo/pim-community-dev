<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Manager;

/**
 * Resolves expected values for attributes
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeValuesResolverInterface
{
    public function resolveEligibleValues(array $attributes, array $channels = null, array $locales = null) : array;
}
