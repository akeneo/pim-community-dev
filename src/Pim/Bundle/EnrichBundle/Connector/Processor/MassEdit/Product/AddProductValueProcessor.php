<?php

namespace Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\ProductInterface;
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
    /** @var PropertyAdderInterface */
    protected $propertyAdder;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param PropertyAdderInterface              $propertyAdder
     * @param ValidatorInterface                  $validator
     */
    public function __construct(PropertyAdderInterface $propertyAdder, ValidatorInterface $validator)
    {
        $this->propertyAdder = $propertyAdder;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        $this->addData($product, $actions);

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
     * Add data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     */
    protected function addData(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->propertyAdder->addData($product, $action['field'], $action['value']);
        }
    }
}
