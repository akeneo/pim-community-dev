<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

/**
 * Batch operation to change products status
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatus extends AbstractMassEditAction
{
    /**
     * @var ProductRepositoryInterface $productRepository
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Whether or not to enable products
     * @var boolean $toEnable
     */
    protected $toEnable = true;

    /**
     * @param boolean $toEnable
     *
     * @return ChangeStatus
     */
    public function setToEnable($toEnable)
    {
        $this->toEnable = $toEnable;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isToEnable()
    {
        return $this->toEnable;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_change_status';
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $productIds)
    {
        $products = $this->productRepository->findBy(array('id' => $productIds));
        foreach ($products as $product) {
            $product->setEnabled($this->toEnable);
        }
    }
}
