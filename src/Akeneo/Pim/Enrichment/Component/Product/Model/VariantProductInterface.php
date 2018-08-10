<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Variant product. An entity that belongs to a family variant and that contains flexible values,
 * completeness, categories, associations and much more...
 *
 * @deprecated Will be removed in 3.0.
 *             Please use ProductInterface::isVariant() to determine is a product is variant or not.
 *
 * @author    Julien Janvier <j.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VariantProductInterface extends ProductInterface
{
}
