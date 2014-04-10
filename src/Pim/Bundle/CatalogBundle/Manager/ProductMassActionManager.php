<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

/**
 * Product mass action manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionManager
{
    /**
     * @var ProductMassActionRepositoryInterface $productRepository
     */
    protected $massActionRepository;

    /**
     * @var AttributeRepository $attributeRepository
     */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepository                  $attributeRepository
     */
    public function __construct(
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->massActionRepository = $massActionRepository;
        $this->attributeRepository  = $attributeRepository;
    }

    /**
     * Find common attributes
     * Common attributes are:
     *   - not unique (and not identifier)
     *   - without value AND link to family
     *   - with value
     *
     * @param array $productIds
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AbstractAttribute[]
     */
    public function findCommonAttributes(array $productIds)
    {
        $attributeIds = $this->massActionRepository->findCommonAttributeIds($productIds);

        return $this
            ->attributeRepository
            ->findWithGroups(array_unique($attributeIds), array('unique' => 0));
    }
}
