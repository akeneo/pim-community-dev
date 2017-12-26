<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectsRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Component\Catalog\Exception\InvalidAttributeException;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Psr\Log\LoggerInterface;

/**
 * Create a product value collection.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionFactory implements ValueCollectionFactoryInterface
{
    /** @var ValueFactory */
    private $valueFactory;

    /** @var CachedObjectRepositoryInterface */
    private $attributeRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var IdentifiableObjectsRepositoryInterface */
    private $attributeRepository2;

    /**
     * @param ValueFactory                           $valueFactory
     * @param CachedObjectRepositoryInterface        $attributeRepository
     * @param LoggerInterface                        $logger
     * @param IdentifiableObjectsRepositoryInterface $attributeRepository2
     */
    public function __construct(
        ValueFactory $valueFactory,
        CachedObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger,
        IdentifiableObjectsRepositoryInterface $attributeRepository2
    ) {
        $this->valueFactory = $valueFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        $this->attributeRepository2 = $attributeRepository2;
    }

    /**
     * {@inheritdoc}
     *
     * Raw values that correspond to an non existing attribute (that was deleted
     * for instance) are NOT loaded.
     *
     * @see \Pim\Component\Catalog\Normalizer\Storage\Product\ProductValuesNormalizer.php
     *
     * @param array $rawValues
     *
     * @return ValueCollectionInterface
     */
    public function createFromStorageFormat(array $rawValues)
    {
        $values = [];
        $attributes = $this->getAttributes($rawValues);

        foreach ($rawValues as $attributeCode => $channelRawValue) {
            if (isset($attributes[$attributeCode])) {
                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    if ('<all_channels>' === $channelCode) {
                        $channelCode = null;
                    }

                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' === $localeCode) {
                            $localeCode = null;
                        }

                        try {
                            $values[] = $this->valueFactory->create($attributes[$attributeCode], $channelCode, $localeCode, $data);
                        } catch (InvalidOptionException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with the option "%s" that does not exist.',
                                    $e->getPropertyValue()
                                )
                            );
                        } catch (InvalidAttributeException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with an invalid attribute "%s". %s',
                                    $attributeCode,
                                    $e->getMessage()
                                )
                            );
                        } catch (InvalidPropertyException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with the property "%s" that does not exist.',
                                    $e->getPropertyValue()
                                )
                            );
                        }
                    }
                }
            } else {
                $this->logger->warning(
                    sprintf(
                        'Tried to load a product value with the attribute "%s" that does not exist.',
                        $attributeCode
                    )
                );
            }
        }

        return new ValueCollection($values);
    }

    /**
     * @param array $rawValues
     *
     * @return AttributeInterface
     */
    private function getAttributes(array $rawValues)
    {
        $attributeCodes = array_keys($rawValues);
        $attributes = $this->attributeRepository2->findSeveralByIdentifiers($attributeCodes);

        return $attributes;
    }
}
