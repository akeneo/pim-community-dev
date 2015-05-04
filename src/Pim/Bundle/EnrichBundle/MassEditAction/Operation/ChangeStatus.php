<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Mass edit operation to change products status
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatus extends AbstractMassEditOperation
{
    /** @var bool Whether or not to enable products */
    protected $toEnable = false;

    /**
     * @param bool $toEnable
     *
     * @return ChangeStatus
     */
    public function setToEnable($toEnable)
    {
        $this->toEnable = $toEnable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isToEnable()
    {
        return $this->toEnable;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'change-status';
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
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return [
            [
                'field' => 'enabled',
                'value' => $this->isToEnable()
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'update_product_value';
    }
}
