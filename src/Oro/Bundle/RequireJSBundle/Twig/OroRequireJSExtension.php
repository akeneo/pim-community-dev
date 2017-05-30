<?php

namespace Oro\Bundle\RequireJSBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class OroRequireJSExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        $container = $this->container;
        return [
            new \Twig_SimpleFunction('get_requirejs_config', function () use ($container) {
                return $container->get('oro_requirejs_config_provider')->getMainConfig();
            }, ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'requirejs_extension';
    }
}
