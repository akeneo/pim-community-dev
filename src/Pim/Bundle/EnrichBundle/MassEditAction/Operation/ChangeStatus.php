<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

/**
 * Batch operation to change products status
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeStatus extends AbstractMassEditOperation implements
    ConfigurableOperationInterface,
    BatchableOperationInterface
{
    /**
     * Whether or not to enable products
     *
     * @var boolean
     */
    protected $toEnable = false;

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
    public function getAlias()
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

    public function getActions()
    {
        return [
            [
                'field' => 'enable',
                'value' => $this->isToEnable()
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchConfig()
    {
        return addslashes(json_encode([
            'filters' => $this->getFilters(),
            'actions' => $this->getActions()
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'change_status';
    }
}
