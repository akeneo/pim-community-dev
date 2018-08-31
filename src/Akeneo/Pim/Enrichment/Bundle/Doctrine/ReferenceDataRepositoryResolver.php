<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryResolverInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Resolves the repository given a reference data type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRepositoryResolver implements ReferenceDataRepositoryResolverInterface
{
    /** @var ConfigurationRegistryInterface */
    protected $configurationRegistry;

    /** @var RegistryInterface */
    protected $doctrineRegistry;

    /**
     * @param ConfigurationRegistryInterface $configurationRegistry
     * @param RegistryInterface              $doctrineRegistry
     */
    public function __construct(
        ConfigurationRegistryInterface $configurationRegistry,
        RegistryInterface $doctrineRegistry
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($referenceDataType)
    {
        $referenceDataConf = $this->configurationRegistry->get($referenceDataType);
        $referenceDataClass = $referenceDataConf->getClass();

        return $this->doctrineRegistry->getRepository($referenceDataClass);
    }
}
