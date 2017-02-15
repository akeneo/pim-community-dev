<?php

namespace Pim\Component\ReferenceData\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\AbstractAttributeSetter;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSetter extends AbstractAttributeSetter
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /**
     * @param ProductBuilderInterface                  $productBuilder
     * @param AttributeValidatorHelper                 $attrValidatorHelper
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     * @param array                                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);

        $this->repositoryResolver = $repositoryResolver;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope']);
        $this->checkData($attribute, $data);

        if (empty($data)) {
            $referenceData = null;
        } else {
            $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());
            $referenceData = $repository->findOneBy(['code' => $data]);

            if (null === $referenceData) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'reference data code',
                    sprintf('The code of the reference data "%s" does not exist', $attribute->getReferenceDataName()),
                    static::class,
                    $data
                );
            }
        }

        $this->setReferenceData($attribute, $product, $referenceData, $options['locale'], $options['scope']);
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }
    }

    /**
     * Set reference data into the product value
     *
     * @param AttributeInterface          $attribute
     * @param ProductInterface            $product
     * @param ReferenceDataInterface|null $referenceData
     * @param string|null                 $locale
     * @param string|null                 $scope
     *
     * @throws \LogicException
     */
    protected function setReferenceData(
        AttributeInterface $attribute,
        ProductInterface $product,
        $referenceData = null,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope);
        }

        $setMethod = MethodNameGuesser::guess('set', $attribute->getReferenceDataName(), true);

        if (!method_exists($value, $setMethod)) {
            throw new \LogicException(
                sprintf('ProductValue method "%s" is not implemented', $setMethod)
            );
        }

        $value->$setMethod($referenceData);
    }
}
