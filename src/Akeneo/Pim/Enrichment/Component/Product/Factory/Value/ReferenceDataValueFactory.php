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
class ReferenceDataValueFactory extends AbstractValueFactory
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
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            return;
        }

        if (!\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

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

        return $referenceData->getCode();
    }
}
