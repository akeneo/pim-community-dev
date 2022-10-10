<?php

namespace Oro\Bundle\DataGridBundle\Twig;

use Oro\Bundle\DataGridBundle\Datagrid\MetadataParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MetadataExtension extends AbstractExtension
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
            new TwigFunction('oro_datagrid_data', [$this->metadataParser, 'getGridData']),
            new TwigFunction('oro_datagrid_metadata', [$this->metadataParser, 'getGridMetadata'])
        ];
    }
}
