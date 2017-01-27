<?php

namespace Pim\Component\ReferenceData\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Factory\ProductValue\ProductValueFactoryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
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
        $this->repositoryResolver = $repositoryResolver;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getReferenceDataCollection($attribute, $data)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
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
        if (null === $data || [] === $data) {
            return;
        }

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
     * Gets a collection of reference data from an array of codes.
     *
     * @param AttributeInterface $attribute
     * @param array              $referenceDataCodes
     *
     * @return ArrayCollection
     */
    protected function getReferenceDataCollection(AttributeInterface $attribute, array $referenceDataCodes)
    {
        $collection = new ArrayCollection();

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($referenceDataCodes as $referenceDataCode) {
            $collection->add($this->getReferenceData($attribute, $repository, $referenceDataCode));
        }

        return $collection;
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
}
