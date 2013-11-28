<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

use Pim\Bundle\CatalogBundle\Form\Type\MassEditAction\ChangeStatusType;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

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
     * @var FlexibleManager $manager
     */
    protected $manager;

    /**
     * @var boolean Wether or not to enable products
     */
    protected $toEnable = true;

    /**
     * @param FlexibleManager $manager
     */
    public function __construct(FlexibleManager $manager)
    {
        $this->manager = $manager;
    }

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
        return new ChangeStatusType();
    }

    /**
     * {@inheritdoc}
     */
    public function perform(array $products)
    {
        foreach ($products as $product) {
            $product->setEnabled($this->toEnable);
        }
        $this->manager->getStorageManager()->flush();
    }
}
