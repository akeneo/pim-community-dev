<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Basic implementation of a Mass Edit Operation Processor. It handles the modification to apply on items.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProcessor extends AbstractConfigurableStepElement implements
    StepExecutionAwareInterface,
    ItemProcessorInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function process($item);

    /**
     * @param ConstraintViolationListInterface $violations
     * @param mixed                            $item
     */
    protected function addWarningMessage(ConstraintViolationListInterface $violations, $item)
    {
        foreach ($violations as $violation) {
            // TODO re-format the message, property path doesn't exist for class constraint
            // for instance cf VariantGroupAxis
            $invalidValue = $violation->getInvalidValue();
            if (is_object($invalidValue) && method_exists($invalidValue, '__toString')) {
                $invalidValue = (string) $invalidValue;
            } elseif (is_object($invalidValue)) {
                $invalidValue = get_class($invalidValue);
            }
            $errors = sprintf(
                "%s: %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $invalidValue
            );
            $this->stepExecution->addWarning($errors, [], $item);
        }
    }

    /**
     * @return array|null
     */
    protected function getConfiguredActions()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return $jobParameters->get('actions');
    }
}
