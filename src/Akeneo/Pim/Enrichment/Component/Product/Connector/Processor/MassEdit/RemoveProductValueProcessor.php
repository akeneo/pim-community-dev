<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to remove product value in a mass edit
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductValueProcessor extends AbstractProcessor
{
    /** @var PropertyRemoverInterface */
    protected $propertyRemover;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param PropertyRemoverInterface $propertyRemover
     * @param ValidatorInterface       $validator
     */
    public function __construct(
        PropertyRemoverInterface $propertyRemover,
        ValidatorInterface $validator
    ) {
        $this->propertyRemover = $propertyRemover;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        $this->removeValuesFromProduct($product, $actions);

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
     * Set data from $actions to the given $product
     *
     * @param ProductInterface|ProductModelInterface $product
     * @param array                                  $actions
     */
    protected function removeValuesFromProduct($product, array $actions)
    {
        foreach ($actions as $action) {
            $this->propertyRemover->removeData($product, $action['field'], $action['value']);
        }
    }
}
