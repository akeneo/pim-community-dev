<?php

namespace Oro\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\MetadataParser;
use Twig_Extension;
use Twig_SimpleFunction;

class MetadataExtension extends Twig_Extension
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(MetadataParser $metadataParser)
    {
        $this->metadataParser = $metadataParser;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('oro_datagrid_data', [$this->metadataParser, 'getGridData']),
            new Twig_SimpleFunction('oro_datagrid_metadata', [$this->metadataParser, 'getGridMetadata'])
        ];
    }
}
