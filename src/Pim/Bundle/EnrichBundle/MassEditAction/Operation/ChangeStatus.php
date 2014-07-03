<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Batch operation to change products status
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatus extends ProductMassEditOperation
{
    /**
     * Whether or not to enable products
     * @var boolean
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
    protected function doPerform(ProductInterface $product)
    {
        $product->setEnabled($this->toEnable);
    }
}
