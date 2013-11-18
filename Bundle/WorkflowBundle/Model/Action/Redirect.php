<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class Redirect extends AbstractAction
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $redirectPath;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param ContextAccessor $contextAccessor
     * @param RouterInterface $router
     * @param string $redirectPath
     */
    public function __construct(ContextAccessor $contextAccessor, RouterInterface $router, $redirectPath)
    {
        parent::__construct($contextAccessor);

        $this->router = $router;
        $this->redirectPath = $redirectPath;
    }

    /**
     * {@inheritDoc}
     */
    protected function executeAction($context)
    {
        $route = $this->getRoute($context);
        if ($route) {
            $routeParameters = $this->getRouteParameters($context);
            $url = $this->router->generate($route, $routeParameters);
        } else {
            $url = $this->getUrl($context);
        }

        $urlProperty = new PropertyPath($this->redirectPath);
        $this->contextAccessor->setValue($context, $urlProperty, $url);
    }

    /**
     * Allowed options:
     *  - url (optional) - direct URL that will be used to perform redirect
     *  - route (optional) - route used to generate url
     *  - route_parameters (optional) - route parameters
     *
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['url']) && empty($options['route'])) {
            throw new InvalidParameterException('Either url or route parameter must be specified');
        }

        if (!empty($options['route_parameters']) && !is_array($options['route_parameters'])) {
            throw new InvalidParameterException('Route parameters must be an array');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @param mixed $context
     * @return string|null
     */
    protected function getUrl($context)
    {
        return !empty($this->options['url'])
            ? $this->contextAccessor->getValue($context, $this->options['url'])
            : null;
    }

    /**
     * @param mixed $context
     * @return string|null
     */
    protected function getRoute($context)
    {
        return !empty($this->options['route'])
            ? $this->contextAccessor->getValue($context, $this->options['route'])
            : null;
    }

    /**
     * @param mixed $context
     * @return array
     */
    protected function getRouteParameters($context)
    {
        $routeParameters = $this->getOption($this->options, 'route_parameters', array());

        foreach ($routeParameters as $name => $value) {
            $routeParameters[$name] = $this->contextAccessor->getValue($context, $value);
        }

        return $routeParameters;
    }
}
