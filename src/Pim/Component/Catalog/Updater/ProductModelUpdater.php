<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelUpdater implements ObjectUpdaterInterface
{
    /** @var PropertySetterInterface */
    private $propertySetter;

    /** @var ObjectUpdaterInterface */
    private $valuesUpdater;

    /** @var array */
    private $supportedFields;

    /** @var array */
    private $ignoredFields;

    /**
     * @param PropertySetterInterface         $propertySetter
     * @param ObjectUpdaterInterface          $valuesUpdater
     * @param array                           $supportedFields
     * @param array                           $ignoredFields
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        array $supportedFields,
        array $ignoredFields
    ) {
        $this->propertySetter = $propertySetter;
        $this->valuesUpdater = $valuesUpdater;
        $this->supportedFields = $supportedFields;
        $this->ignoredFields = $ignoredFields;
    }

    /**
     * {@inheritdoc}
     */
    public function update($productModel, array $data, array $options = [])
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($productModel),
                ProductInterface::class
            );
        }

        foreach ($data as $code => $values) {
            if (in_array($code, $this->supportedFields)) {
                $this->propertySetter->setData($productModel, $code, $values);
            } elseif ('values' === $code) {
                $this->valuesUpdater->update($productModel, $values, $options);
            } elseif ('identifier' === $code) {
                $productModel->setIdentifier($values);
            } elseif (!in_array($code, $this->ignoredFields)) {
                throw UnknownPropertyException::unknownProperty($code);
            }
        }

        return $this;
    }
}
