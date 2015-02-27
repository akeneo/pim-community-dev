<?php

namespace Pim\Bundle\BaseConnectorBundle\Step;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\AbstractStep;
use Pim\Bundle\BaseConnectorBundle\Validator\Step\CharsetValidator;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation;

/**
 * Step for mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditStep extends AbstractStep
{
    /** @var ProductMassEditOperation */
    protected $operation;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->operation->setStepExecution($stepExecution);
        $this->operation->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = [];
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                foreach ($stepElement->getConfiguration() as $key => $value) {
                    if (!isset($configuration[$key]) || $value) {
                        $configuration[$key] = $value;
                    }
                }
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
        }
    }

    /**
     * @return ProductMassEditOperation
     */
    public function getProductMassEditOperation()
    {
        return $this->operation;
    }

    /**
     * @param ProductMassEditOperation $operation
     */
    public function setProductMassEditOperation(ProductMassEditOperation $operation)
    {
        $this->operation = $operation;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableStepElements()
    {
        return ['productMassEditOperation' => $this->getProductMassEditOperation()];
    }
}
