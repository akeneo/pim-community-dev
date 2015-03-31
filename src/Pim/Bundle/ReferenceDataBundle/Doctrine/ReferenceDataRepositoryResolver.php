<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine;

use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Resolves the repository of a reference data
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRepositoryResolver
{
    /** @var ConfigurationRegistryInterface */
    protected $configurationRegistry;

    /** @var RegistryInterface */
    protected $doctrineRegistry;

    public function __construct(
        ConfigurationRegistryInterface $configurationRegistry,
        RegistryInterface $doctrineRegistry
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->doctrineRegistry      = $doctrineRegistry;
    }

    /**
     * @param $referenceData
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function resolve($referenceData)
    {
        $referenceDataConf = $this->configurationRegistry->get($referenceData);
        $referenceDataClass = $referenceDataConf->getClass();

        return $this->doctrineRegistry->getRepository($referenceDataClass);
    }
}
