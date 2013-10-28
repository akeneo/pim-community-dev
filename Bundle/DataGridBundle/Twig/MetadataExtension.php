<?php

namespace Oro\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;

class MetadataExtension extends \Twig_Extension
{
    /** @var Manager */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oro_datagrid_metadata';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return ['oro_datagrid_metadata' => new \Twig_Function_Method($this, 'getGridMetadata')];
    }

    /**
     * Returns grid metadata array
     *
     * @param string $name
     * @param array  $additionalParams
     *
     * @return \stdClass
     */
    public function getGridMetadata($name, $additionalParams = [])
    {
        $metaData            = $this->manager->getDatagrid($name)->getMetadata();
        $metaData->offsetAddToArray('options', $additionalParams);

        return $metaData->toArray();
    }
}
