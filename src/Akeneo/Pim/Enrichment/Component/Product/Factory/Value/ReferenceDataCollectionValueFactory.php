<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

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
class ReferenceDataCollectionValueFactory extends AbstractValueFactory
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    public function __construct(
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        string $productValueClass,
        string $supportedAttributeType
    ) {
        parent::__construct($productValueClass, $supportedAttributeType);

        $this->repositoryResolver = $repositoryResolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData = false)
    {
        if (null === $data) {
            $data = [];
        }

        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!\is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }

        $referenceDataCodes = [];

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($data as $referenceDataCode) {
            $referenceData = $repository->findOneBy(['code' => $referenceDataCode]);

            if (null === $referenceData) {
                if (!$ignoreUnknownData) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        $attribute->getCode(),
                        'reference data code',
                        sprintf('The code of the reference data "%s" does not exist', $attribute->getReferenceDataName()),
                        static::class,
                        $referenceDataCode
                    );
                }
            } else {
                $referenceDataCodes[] = $referenceData->getCode();
            }
        }

        $referenceDataCodes = \array_unique($referenceDataCodes);

        \sort($referenceDataCodes);

        return $referenceDataCodes;
    }
}
