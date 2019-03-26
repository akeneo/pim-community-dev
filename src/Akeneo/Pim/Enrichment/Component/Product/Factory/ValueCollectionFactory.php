<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOptionException;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOptionsException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
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

    /** @var LruArrayAttributeRepository */
    private $attributeRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ValueFactory $valueFactory,
        LruArrayAttributeRepository $attributeRepository,
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
     * @see \Akeneo\Pim\Enrichment\Component\Product\Normalizer\Storage\Product\ProductValuesNormalizer.php
     *
     * @param array $rawValues
     *
     * @return ValueCollectionInterface
     */
    public function createFromStorageFormat(array $rawValues)
    {
        $values = [];

        $attributes = $this->attributeRepository->findSeveralByIdentifiers(array_keys($rawValues));

        foreach ($rawValues as $attributeCode => $channelRawValue) {
            $attribute = $attributes[$attributeCode] ?? null;

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
                            $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data, true);
                        } catch (InvalidOptionException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with the option "%s" that does not exist.',
                                    $e->getPropertyValue()
                                )
                            );
                        } catch (InvalidOptionsException $e) {
                            $this->logger->warning(
                                sprintf(
                                    'Tried to load a product value with the options "%s" that do not exist.',
                                    $e->toString()
                                )
                            );
                            $goodOptions = array_diff($data, $e->toArray());
                            if (!empty($goodOptions)) {
                                $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $goodOptions);
                            }
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
