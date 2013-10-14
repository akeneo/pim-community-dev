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
        return 'oro_grid_metadata';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            'oro_grid_metadata' => new \Twig_Function_Method($this, 'getGridMetadata'),
        );
    }

    /**
     * Returns grid metadata array
     *
     * @param string $name
     *
     * @return \stdClass
     */
    public function getGridMetadata($name)
    {
        return $this->manager->getDatagrid($name)->getMetadata();
    }
}
