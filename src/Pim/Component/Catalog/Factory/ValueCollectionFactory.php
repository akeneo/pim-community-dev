<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidAttributeException;
use Pim\Component\Catalog\Exception\InvalidOptionException;
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

    /**
     * @param ValueFactory                    $valueFactory
     * @param CachedObjectRepositoryInterface $attributeRepository
     * @param LoggerInterface                 $logger
     */
    public function __construct(
        ValueFactory $valueFactory,
        CachedObjectRepositoryInterface $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->valueFactory = $valueFactory;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
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

        foreach ($rawValues as $attributeCode => $channelRawValue) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $attribute) {
                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    if ('<all_channels>' === $channelCode) {
                        $channelCode = null;
                    }

                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' === $localeCode) {
                            $localeCode = null;
                        }

                        try {
                            $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data);
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
                        } catch (InvalidPropertyTypeException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value for attribute "%s" that does not have the ' .
                                    'good type in database.',
                                    $attribute->getCode()
                                )
                            );
                            $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, null);
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
}
