<?php

namespace Pim\Bundle\GridBundle\Twig;

use Pim\Bundle\GridBundle\Route\DatagridRouteRegistry;

/**
 * Gives access to datagrid route regexps in twig templates
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridExtension extends \Twig_Extension
{
    /**
     * @var DatagridRouteRegistry
     */
    protected $routeRegistry;

    /**
     * Constructor
     *
     * @param DatagridRouteRegistry $routeRegistry
     */
    public function __construct(DatagridRouteRegistry $routeRegistry)
    {
        $this->routeRegistry = $routeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_grid';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'grid_route_regexps' => new \Twig_Function_Method(
                $this,
                'getGridRouteRegexps',
                array('is_safe' => array('js', 'html'))
            )
        );
    }

    /**
     * Return a json encoded collection of grid route regexps
     *
     * @return string
     */
    public function getGridRouteRegexps()
    {
        $members = array();
        foreach ($this->routeRegistry->getRegexps() as $datagridName => $regexp) {
            $members[] = sprintf('"%s": %s', $datagridName, $regexp);
        }

        return sprintf('{%s}', implode(",\n", $members));
    }
}
