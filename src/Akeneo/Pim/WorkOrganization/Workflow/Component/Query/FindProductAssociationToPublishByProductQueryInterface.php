<?php
/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindProductAssociationToPublishByProductQueryInterface
{
    public const PRODUCT_ID = 'product_id';
    public const ASSOCIATION_TYPE_ID = 'association_type_id';

    public function execute(ProductInterface $product): array;
}
