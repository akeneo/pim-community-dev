<?php

namespace Pim\Component\ReferenceData\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

/**
 * Factory that creates simple-select and multi-select product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionProductValueFactory implements ProductValueFactoryInterface
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     * @param string                                   $productValueClass
     * @param string                                   $supportedAttributeType
     */
    public function __construct(
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        $productValueClass,
        $supportedAttributeType
    ) {
        if (!class_exists($productValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $productValueClass)
            );
        }

        $this->repositoryResolver = $repositoryResolver;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        $value = new $this->productValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        $adder = $this->getAdderName($value, $attribute);
        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($data as $referenceDataCode) {
            $value->$adder($this->getReferenceData($attribute, $repository, $referenceDataCode));
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks if data is valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidArgumentException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'reference data collection',
                'factory',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringKeyExpected(
                    $attribute->getCode(),
                    $key,
                    'reference data collection',
                    'factory',
                    gettype($value)
                );
            }
        }
    }

    /**
     * Finds a reference data by code.
     *
     * @param AttributeInterface               $attribute
     * @param ReferenceDataRepositoryInterface $repository
     * @param string                           $referenceDataCode
     *
     * @throws InvalidPropertyException
     * @return ReferenceDataInterface
     */
    protected function getReferenceData(
        AttributeInterface $attribute,
        ReferenceDataRepositoryInterface $repository,
        $referenceDataCode
    ) {
        $referenceData = $repository->findOneBy(['code' => $referenceDataCode]);

        if (null === $referenceData) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                sprintf(
                    'No reference data "%s" with code "%s" has been found',
                    $attribute->getReferenceDataName(),
                    $referenceDataCode
                ),
                'reference data collection',
                'factory',
                $referenceDataCode
            );
        }

        return $referenceData;
    }

    /**
     * @param ProductValueInterface $value
     * @param AttributeInterface    $attribute
     *
     * @return string
     */
    private function getAdderName(
        ProductValueInterface $value,
        AttributeInterface $attribute
    ) {
        $method = MethodNameGuesser::guess('add', $attribute->getReferenceDataName(), true);

        if (!method_exists($value, $method)) {
            throw new \LogicException(
                sprintf('ProductValue method "%s" is not implemented', true)
            );
        }

        return $method;
    }
}
