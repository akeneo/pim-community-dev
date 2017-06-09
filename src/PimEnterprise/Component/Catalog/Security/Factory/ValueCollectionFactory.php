<?php

namespace PimEnterprise\Component\Catalog\Security\Factory;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Decorates the CE factory to be able to get only granted values. On a value, permission can be added on:
 *  - an attribute. You cannot see an attribute if it belongs to a not granted attribute group
 *  - a locale. Permission can be added directly on it
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionFactory implements ValueCollectionFactoryInterface
{
    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var LoggerInterface */
    private $logger;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /**
     * @param ValueCollectionFactoryInterface       $valueCollectionFactory
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param LoggerInterface                       $logger
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        ValueCollectionFactoryInterface $valueCollectionFactory,
        AuthorizationCheckerInterface $authorizationChecker,
        LoggerInterface $logger,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @see Pim\Component\Catalog\Factory\ValueCollectionFactory
     */
    public function createFromStorageFormat(array $rawValues)
    {
        if (empty($rawValues)) {
            return $this->valueCollectionFactory->createFromStorageFormat($rawValues);
        }

        $rawValuesFiltered = [];
        foreach ($rawValues as $attributeCode => $values) {
            $isGrantedAttribute = $this->isGrantedAttribute($attributeCode);
            if ($isGrantedAttribute) {
                $grantedValues = $this->getGrantedValueLocalizable($values);

                if (!empty($grantedValues)) {
                    $rawValuesFiltered[$attributeCode] = $grantedValues;
                }
            }
        }

        return $this->valueCollectionFactory->createFromStorageFormat($rawValuesFiltered);
    }

    /**
     * Get only granted localizable values (so at least viewable) or non localizable values
     *
     * @param array $values
     *
     * @return array
     */
    private function getGrantedValueLocalizable(array $values)
    {
        foreach ($values as $channelCode => $localeRawValue) {
            foreach ($localeRawValue as $localeCode => $data) {
                if ('<all_locales>' !== $localeCode) {
                    $locale = $this->localeRepository->findOneByIdentifier($localeCode);
                    if (null === $locale) {
                        $this->logger->warning(
                            sprintf(
                                'Tried to load a product value with the locale "%s" that does not exist.',
                                $localeCode
                            )
                        );

                        unset($values[$channelCode][$localeCode]);
                    } elseif (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                        unset($values[$channelCode][$localeCode]);
                    }
                }
            }

            if (empty($values[$channelCode])) {
                unset($values[$channelCode]);
            }
        }

        return $values;
    }

    /**
     * Check if attribute is granted
     *
     * @param string $attributeCode
     *
     * @return bool
     */
    private function isGrantedAttribute($attributeCode)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            $this->logger->warning(
                sprintf(
                    'Tried to load a product value with the attribute "%s" that does not exist.',
                    $attributeCode
                )
            );

            return false;
        }

        return $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute);
    }
}
