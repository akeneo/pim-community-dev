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

    public function getFunctions()
    {
        return array(
            'grid_route_regexps' => new \Twig_Function_Method(
                $this,
                'getRouteRegexps',
                array('is_safe' => array('html'))
            )
        );
    }

    public function getRouteRegexps()
    {
        $members = array();
        foreach ($this->routeRegistry->getRegexps() as $datagridName => $regexp) {
            $members[] = sprintf('"%s": %s', $datagridName, $this->getJsRegexp($regexp));
        }

        return sprintf('{%s}', implode(",\n", $members));
    }

    protected function getJsRegexp($regexp)
    {
        return $regexp;
        preg_match('/^#(.+)#/', $regexp, $matches);
        return sprintf('/%s/', str_replace('/', '\\/', str_replace('(?:/(?P<_format>html|json))?', '', $matches[1])));
    }
}
