<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to update product value in a mass edit
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductValueProcessor extends AbstractProcessor
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param PropertySetterInterface             $propertySetter
     * @param ValidatorInterface                  $validator
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator
    ) {
        $this->propertySetter = $propertySetter;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $actions = $this->getConfiguredActions();
        $this->setData($product, $actions);

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
    protected function setData($product, array $actions)
    {
        foreach ($actions as $action) {
            $this->propertySetter->setData($product, $action['field'], $action['value']);
        }
    }
}
