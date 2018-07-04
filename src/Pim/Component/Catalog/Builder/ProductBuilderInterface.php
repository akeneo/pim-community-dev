<?php

namespace Pim\Component\Catalog\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;

/**
 * Product builder interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductBuilderInterface extends EntityWithValuesBuilderInterface
{
    /**
     * Create product with its identifier value,
     *  - sets the identifier data if provided
     *  - sets family if provided
     *
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    public function createProduct($identifier = null, $familyCode = null);

    /**
     * Add empty associations for each association types when they don't exist yet
     *
     * @param EntityWithAssociationsInterface $entity
     *
     * @return EntityWithValuesBuilderInterface
     *
     * @deprecated since 2.3 in favor of \Pim\Component\Catalog\Association\MissingAssociationAdder
     */
    public function addMissingAssociations(EntityWithAssociationsInterface $entity);
}
