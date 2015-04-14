<?php

namespace Pim\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Handler\UpdateProductHandler;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Processor to update product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductValueProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ProductUpdaterInterface $productUpdater
     * @param ValidatorInterface      $validator
     */
    public function __construct(ProductUpdaterInterface $productUpdater, ValidatorInterface $validator)
    {
        $this->productUpdater = $productUpdater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $actions = $item['actions'];
        $product = $item['product'];
        $this->setData($product, $actions);
        $violations = $this->validator->validate($product);

        if (0 < $violations->count()) {
            $this->addWarningMessage($violations, $product);
            $this->stepExecution->incrementSummaryInfo('skipped_products');
            return null;
        } else {
            $this->stepExecution->incrementSummaryInfo('mass_edited');
        }

        return $product;
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
     * Set data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return UpdateProductHandler
     */
    protected function setData(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->productUpdater->setData($product, $action['field'], $action['value']);
        }

        return $this;
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage(
        ConstraintViolationListInterface $violations,
        ProductInterface $product
    ) {
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
}
