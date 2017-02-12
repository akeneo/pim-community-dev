<?php

namespace Pim\Component\ReferenceData\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\Setter\AbstractAttributeSetter;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionSetter extends AbstractAttributeSetter
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

        $refDataCollection = [];
        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($data as $referenceDataCode) {
            $referenceData = $repository->findOneBy(['code' => $referenceDataCode]);

            if (null === $referenceData) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'reference data code',
                    sprintf('The code of the reference data "%s" does not exist', $attribute->getReferenceDataName()),
                    static::class,
                    $referenceDataCode
                );
            }

            $refDataCollection[] = $referenceData;
        }

        $this->setReferenceDataCollection(
            $attribute,
            $product,
            $refDataCollection,
            $options['locale'],
            $options['scope']
        );
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('one of the "%s" values is not a scalar', $attribute->getCode()),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Set reference data collection into the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param array              $refDataCollection
     * @param string|null        $locale
     * @param string|null        $scope
     *
     * @throws \LogicException
     */
    protected function setReferenceDataCollection(
        AttributeInterface $attribute,
        ProductInterface $product,
        array $refDataCollection,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addOrReplaceProductValue($product, $attribute, $locale, $scope);
        }

        $referenceDataName = $attribute->getReferenceDataName();
        $addMethod = MethodNameGuesser::guess('add', $referenceDataName, true);
        $removeMethod = MethodNameGuesser::guess('remove', $referenceDataName, true);
        $getMethod = MethodNameGuesser::guess('get', $referenceDataName);

        if (!method_exists($value, $addMethod) ||
            !method_exists($value, $removeMethod) ||
            !method_exists($value, $getMethod)
        ) {
            throw new \LogicException(
                sprintf(
                    'One of these methods is not implemented in %s: "%s", "%s", "%s"',
                    ClassUtils::getClass($value),
                    $addMethod,
                    $removeMethod,
                    $getMethod
                )
            );
        }

        $currentCollection = $value->$getMethod();

        foreach ($currentCollection as $currentReferenceData) {
            $value->$removeMethod($currentReferenceData);
        }

        foreach ($refDataCollection as $referenceData) {
            $value->$addMethod($referenceData);
        }
    }
}
