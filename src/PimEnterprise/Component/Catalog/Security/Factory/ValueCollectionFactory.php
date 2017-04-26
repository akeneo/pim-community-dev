<?php

namespace PimEnterprise\Component\Catalog\Security\Factory;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Decorates the CE factory to be able to filter values to return only attributes and locales viewable by the user
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCollectionFactory implements ValueCollectionFactoryInterface
{
    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var LoggerInterface */
    protected $logger;

    /** @var CachedObjectRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ValueCollectionFactoryInterface $productValueCollectionFactory
     * @param TokenStorageInterface           $tokenStorage
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param LoggerInterface                 $logger
     * @param CachedObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        ValueCollectionFactoryInterface $valueCollectionFactory,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        LoggerInterface $logger,
        CachedObjectRepositoryInterface $attributeRepository
    ) {
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @see Pim\Component\Catalog\Factory\ValueCollectionFactory
     */
    public function createFromStorageFormat(array $rawValues)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token || empty($rawValues)) {
            return $this->valueCollectionFactory->createFromStorageFormat($rawValues);
        }

        $rawValuesFiltered = [];
        foreach ($rawValues as $attributeCode => $values) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attribute) {
                $this->logger->warning(
                    sprintf(
                        'Tried to load a product value with the attribute "%s" that does not exist.',
                        $attributeCode
                    )
                );

                continue;
            }

            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute)) {
                continue;
            }

            $rawValuesFiltered[$attributeCode] = $values;
        }

        return $this->valueCollectionFactory->createFromStorageFormat($rawValuesFiltered);
    }
}
