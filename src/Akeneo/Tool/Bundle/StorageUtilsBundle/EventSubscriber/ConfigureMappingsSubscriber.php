<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\MappingsOverrideConfiguratorInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;

/**
 * Configure the mappings of the metadata classes.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureMappingsSubscriber implements EventSubscriber
{
    /** @var MappingsOverrideConfiguratorInterface */
    protected $configurator;

    /** @var array */
    protected $mappingOverrides;

    /**
     * Constructor
     *
     * @param MappingsOverrideConfiguratorInterface $configurator
     * @param array                                 $mappingOverrides
     */
    public function __construct(MappingsOverrideConfiguratorInterface $configurator, array $mappingOverrides)
    {
        $this->configurator = $configurator;
        $this->mappingOverrides = $mappingOverrides;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $this->configurator->configure(
            $eventArgs->getClassMetadata(),
            $this->mappingOverrides,
            $eventArgs->getObjectManager()->getConfiguration()
        );
    }
}
