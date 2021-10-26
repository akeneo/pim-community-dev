<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\InvalidReferenceDataException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Persistence\ObjectRepository;

/**
 * Resolves the repository given a reference data type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRepositoryResolver implements ReferenceDataRepositoryResolverInterface
{
    protected ConfigurationRegistryInterface $configurationRegistry;
    protected ManagerRegistry $doctrineRegistry;

    public function __construct(
        ConfigurationRegistryInterface $configurationRegistry,
        ManagerRegistry $doctrineRegistry
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $referenceDataType): ObjectRepository
    {
        if (!$this->configurationRegistry->has($referenceDataType)) {
            throw new InvalidReferenceDataException(\sprintf('The "%s" reference data does not exist', $referenceDataType));
        }
        $referenceDataClass = $this->configurationRegistry->get($referenceDataType)->getClass();

        try {
            return $this->doctrineRegistry->getRepository($referenceDataClass);
        } catch (\ReflectionException|MappingException $e) {
            throw new InvalidReferenceDataException(
                \sprintf('Could not find repository for "%s" reference data', $referenceDataType)
            );
        }
    }
}
