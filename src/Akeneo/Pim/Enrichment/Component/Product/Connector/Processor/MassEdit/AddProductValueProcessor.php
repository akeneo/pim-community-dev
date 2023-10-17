<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to add product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly PropertyAdderInterface $propertyAdder,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        try {
            $this->addData($product, $actions);
        } catch (PropertyException|TwoWayAssociationWithTheSameProductException $e) {
            $this->stepExecution->addWarning($e->getMessage(), [], new DataInvalidItem($product));
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        if (!$this->isProductValid($product)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        return $product;
    }

    /**
     * Validate the product
     *
     * @param ProductInterface|ProductModelInterface $product
     *
     * @return bool
     */
    protected function isProductValid($product)
    {
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }

    /**
     * Add data from $actions to the given $product
     *
     * @param ProductInterface|ProductModelInterface $product
     * @param array                                  $actions
     */
    protected function addData($product, array $actions): void
    {
        foreach ($actions as $action) {
            $this->propertyAdder->addData($product, $action['field'], $action['value']);
        }
    }
}
