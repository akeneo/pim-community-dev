<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

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
     * @var boolean Whether or not to enable products
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
     *
     * TODO: Check with MongoDB implementation
     */
    public function perform($qb)
    {
        $products = $qb->getQuery()->getResult();
        foreach ($products as $product) {
            $product->setEnabled($this->toEnable);
        }
    }
}
