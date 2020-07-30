<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Component\Product\Storage;

use Akeneo\Pim\Structure\Component\Model\Attribute;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductAndProductModelIdsWithValuesIgnoringLocaleAndScope
{
    /**
     * Returns product and product model ids that have at least one value for the given attribute
     * no matter the locale and the scope.
     * It returns multiple array of ids (iterable).
     */
    public function forAttributeAndValues(Attribute $attribute, array $values): iterable;
}
