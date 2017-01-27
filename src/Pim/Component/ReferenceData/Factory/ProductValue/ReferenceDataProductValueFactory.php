<?php

namespace Pim\Component\ReferenceData\Factory\ProductValue;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
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
class ReferenceDataProductValueFactory implements ProductValueFactoryInterface
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

        if (null !== $data) {
            $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());
            $data = $this->getReferenceData($attribute, $repository, $data);
        }

        $value = new $this->productValueClass($attribute, $channelCode, $localeCode, $data);

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

        if (!is_string($data)) {
            throw InvalidArgumentException::stringExpected(
                $attribute->getCode(),
                'reference data',
                'factory',
                gettype($data)
            );
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
                'reference data',
                'factory',
                $referenceDataCode
            );
        }

        return $referenceData;
    }
}
