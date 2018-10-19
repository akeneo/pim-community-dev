<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates simple-select and multi-select product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ReferenceDataValueFactory implements ValueFactoryInterface
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
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data, $ignoreUnknownData = false)
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
                'reference data code',
                sprintf('The code of the reference data "%s" does not exist', $attribute->getReferenceDataName()),
                static::class,
                $referenceDataCode
            );
        }

        return $referenceData;
    }
}
