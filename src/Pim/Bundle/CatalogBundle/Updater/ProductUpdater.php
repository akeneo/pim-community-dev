<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Exception\BusinessValidationException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Updates and validates a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements ProductUpdaterInterface
{
    /** @var ProductFieldUpdaterInterface */
    protected $productFieldUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ProductFieldUpdaterInterface $productFieldUpdater
     * @param ValidatorInterface           $validator
     */
    public function __construct(
        ProductFieldUpdaterInterface $productFieldUpdater,
        ValidatorInterface $validator
    ) {
        $this->productFieldUpdater = $productFieldUpdater;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BusinessValidationException
     */
    public function update($product, array $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }

        $updateViolations = new ConstraintViolationList();
        try {
            foreach ($data as $field => $values) {
                if (in_array($field, ['enabled', 'family', 'categories', 'groups', 'associations'])) {
                    $this->productFieldUpdater->setData($product, $field, $values, []);
                } else {
                    $this->updateProductValues($product, $field, $values);
                }
            }
        } catch (\InvalidArgumentException $e) {
            $setViolation = new ConstraintViolation(
                $e->getMessage(),
                $e->getMessage(),
                [],
                $product,
                null,
                null
            );
            $updateViolations->add($setViolation);
        }

        $validatorViolations = $this->validator->validate($product);
        $updateViolations->addAll($validatorViolations);
        if ($updateViolations->count() > 0) {
            throw new BusinessValidationException($updateViolations);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5, please use ProductFieldUpdaterInterface::setData(
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null)
    {
        foreach ($products as $product) {
            $this->productFieldUpdater->setData($product, $field, $data, ['locale' => $locale, 'scope' => $scope]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5, please use ProductFieldUpdaterInterface::copyData(
     */
    public function copyValue(
        array $products,
        $fromField,
        $toField,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        $options = [
            'from_locale' => $fromLocale,
            'to_locale' => $toLocale,
            'from_scope' => $fromScope,
            'to_scope' => $toScope,
        ];
        foreach ($products as $product) {
            $this->productFieldUpdater->copyData($product, $product, $fromField, $toField, $options);
        }

        return $this;
    }

    /**
     * Sets the value if the attribute belongs to the family or if the value already exists as optional
     *
     * @param ProductInterface $product
     * @param string           $field
     * @param array            $values
     */
    protected function updateProductValues(ProductInterface $product, $field, array $values)
    {
        foreach ($values as $value) {
            $family = $product->getFamily();
            $belongsToFamily = $family === null ? false : $family->hasAttributeCode($field);
            $hasValue = $product->getValue($field, $value['locale'], $value['scope']) !== null;
            if ($belongsToFamily || $hasValue) {
                $options = ['locale' => $value['locale'], 'scope' => $value['scope']];
                $this->productFieldUpdater->setData($product, $field, $value['data'], $options);
            }
        }
    }
}
