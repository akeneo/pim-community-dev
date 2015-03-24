<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\MethodNameGuesser;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionSetter extends AbstractAttributeSetter
{
    /** @var DenormalizerInterface */
    protected $referenceDataDenormalizer;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param DenormalizerInterface    $referenceDataDenormalizer
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        DenormalizerInterface $referenceDataDenormalizer,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);

        $this->referenceDataDenormalizer = $referenceDataDenormalizer;
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

        $data = $this->referenceDataDenormalizer->denormalize($data, '', null, ['attribute' => $attribute]);

        $this->setReferenceDataCollection($attribute, $product, $data, $options['locale'], $options['scope']);
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'setter',
                'reference data collection',
                gettype($data)
            );
        }
    }

    /**
     * Set reference data collection into the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param ArrayCollection    $referenceDataCollection
     * @param string|null        $locale
     * @param string|null        $scope
     *
     * @throws \LogicException
     */
    protected function setReferenceDataCollection(
        AttributeInterface $attribute,
        ProductInterface $product,
        ArrayCollection $referenceDataCollection,
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
