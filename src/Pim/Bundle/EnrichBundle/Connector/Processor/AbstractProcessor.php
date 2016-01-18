<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
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

    /** @var JobConfigurationRepositoryInterface */
    protected $jobConfigurationRepo;

    /**
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     */
    public function __construct(JobConfigurationRepositoryInterface $jobConfigurationRepo)
    {
        $this->jobConfigurationRepo = $jobConfigurationRepo;
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
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    abstract public function process($item);

    /**
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage(ConstraintViolationListInterface $violations, ProductInterface $product)
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
            $this->stepExecution->addWarning($this->getName(), $errors, [], $product);
        }
    }

    /**
     * Return the job configuration
     *
     * @return array
     */
    protected function getJobConfiguration()
    {
        $jobExecution    = $this->stepExecution->getJobExecution();
        $massEditJobConf = $this->jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution]);

        return json_decode(stripcslashes($massEditJobConf->getConfiguration()), true);
    }
}
