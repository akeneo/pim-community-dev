<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Pim\Component\ReferenceData\MethodNameGuesser;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionSetter extends AbstractAttributeSetter
{
    /** @var ReferenceDataRepositoryResolver */
    protected $repositoryResolver;

    /**
     * @param ProductBuilderInterface         $productBuilder
     * @param AttributeValidatorHelper        $attrValidatorHelper
     * @param ReferenceDataRepositoryResolver $repositoryResolver
     * @param array                           $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        ReferenceDataRepositoryResolver $repositoryResolver,
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
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'reference data collection');
        $this->checkData($attribute, $data);

        $referenceDataCollection = [];
        foreach ($data as $referenceDataCode) {

            $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());
            $referenceData = $repository->findOneBy(['code' => $referenceDataCode]);

            if (null === $referenceData) {
                throw InvalidArgumentException::arrayInvalidKey(
                    $attribute->getCode(),
                    'code',
                    'The reference data does not exist',
                    'setter',
                    'reference data collection',
                    $referenceDataCode
                );
            }

            $referenceDataCollection[] = $referenceData;
        }


        $this->setReferenceDataCollection($attribute, $product, $referenceDataCollection, $options['locale'], $options['scope']);
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
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'setter',
                'reference data collection',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringKeyExpected(
                    $attribute->getCode(),
                    $key,
                    'setter',
                    'reference data collection',
                    gettype($value)
                );
            }
        }
    }

    /**
     * Set reference data collection into the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param array              $referenceDataCollection
     * @param string|null        $locale
     * @param string|null        $scope
     *
     * @throws \LogicException
     */
    protected function setReferenceDataCollection(
        AttributeInterface $attribute,
        ProductInterface $product,
        array $referenceDataCollection,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        $referenceDataName = $attribute->getReferenceDataName();
        $addMethod = MethodNameGuesser::guess('add', $referenceDataName, true);
        $removeMethod = MethodNameGuesser::guess('remove', $referenceDataName, true);
        $getMethod = MethodNameGuesser::guess('get', $referenceDataName);

        if (false === method_exists($value, $addMethod) ||
            false === method_exists($value, $removeMethod) ||
            false === method_exists($value, $getMethod)
        ) {
            throw new \LogicException(
                sprintf(
                    'One of these ProductValue methods is not implemented: "%s", "%s", "%s"',
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

        foreach ($referenceDataCollection as $referenceData) {
            $value->$addMethod($referenceData);
        }
    }
}
