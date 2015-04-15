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
class UpdateProductValueProcessor extends AbstractMassEditProcessor implements ItemProcessorInterface
{
    /** @var ProductUpdaterInterface */
    protected $productUpdater;

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
     * Set data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return UpdateProductValueProcessor
     */
    protected function setData(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->productUpdater->setData($product, $action['field'], $action['value']);
        }

        return $this;
    }
}
