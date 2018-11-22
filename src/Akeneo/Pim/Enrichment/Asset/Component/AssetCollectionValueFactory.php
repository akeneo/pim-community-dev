<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Asset\Component;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\AbstractValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Copy of the {@see Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ReferenceDataCollectionValueFactory} but without
 * sorting data.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ProductValueFactory.
 *
 * @author    Julien Janvier (j.janvier@gmail.com)
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssetCollectionValueFactory extends AbstractValueFactory
{
    /** @var ReferenceDataRepositoryResolverInterface */
    protected $repositoryResolver;

    /**
     * @param ReferenceDataRepositoryResolverInterface $repositoryResolver
     * @param string                                   $productValueClass
     */
    public function __construct(
        ReferenceDataRepositoryResolverInterface $repositoryResolver,
        $productValueClass
    ) {
        parent::__construct($productValueClass, AttributeTypes::ASSETS_COLLECTION);

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
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }

        $referenceDataCodes = [];

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());

        foreach ($data as $code) {
            $referenceData = $repository->findOneBy(['code' => $code]);

            if (null === $referenceData) {
                if (false === $ignoreUnknownData) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        $attribute->getCode(),
                        'reference data code',
                        sprintf('The code of the reference data "%s" does not exist', $attribute->getReferenceDataName()),
                        static::class,
                        $code
                    );
                }
            } elseif (!in_array($referenceData->getCode(), $referenceDataCodes, true)) {
                $referenceDataCodes[] = $referenceData->getCode();
            }
        }

        return $referenceDataCodes;
    }
}
