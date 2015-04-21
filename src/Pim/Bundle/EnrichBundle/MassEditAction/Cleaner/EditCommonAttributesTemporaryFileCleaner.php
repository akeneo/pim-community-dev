<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Cleaner;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * BatchBundle step element, it applies the mass edit common attributes
 * to products given in configuration.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesTemporaryFileCleaner extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $actions = $configuration['actions'];

        $values = [];
        foreach ($actions as $action) {
            if (isset($action['value'])) {
                $values[] = $action['value'];
            }
        }

        $this->removeTemporaryFiles($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Remove temporary files used to set product media
     *
     * @param array $values
     */
    protected function removeTemporaryFiles(array $values)
    {
        foreach ($values as $value) {
            if (isset($value['filePath'])) {
                unlink($value['filePath']);
            }
        }
    }
}
