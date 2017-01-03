<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\ProductInterface;
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
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isProductValid(ProductInterface $product)
    {
        $violations = $this->validator->validate($product);
        $this->addWarningMessage($violations, $product);

        return 0 === $violations->count();
    }

    /**
     * Set data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return UpdateProductValueProcessor
     */
    protected function removeValuesFromProduct(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->propertyRemover->removeData($product, $action['field'], $action['value']);
        }
    }
}
