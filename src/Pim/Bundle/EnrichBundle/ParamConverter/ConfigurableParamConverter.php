<?php

namespace Pim\Bundle\EnrichBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigurableParamConverter implements ParamConverterInterface
{
    /** @var Container $container */
    protected $container;

    /** @var ParamConverterInterface $paramConverter */
    protected $paramConverter;

    /**
     * @param Container               $container
     * @param ParamConverterInterface $paramConverter
     */
    public function __construct(Container $container, ParamConverterInterface $paramConverter)
    {
        $this->container      = $container;
        $this->paramConverter = $paramConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        return $this->paramConverter->apply($request, $this->getProperConfiguration($configuration));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (!$configuration instanceof ParamConverter) {
            return false;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        if (!$this->container->hasParameter($configuration->getClass())) {
            return false;
        }

        $properConfiguration = $this->getProperConfiguration($configuration);

        return $this->paramConverter->supports($properConfiguration);
    }

    /**
     * Get a proper class name for the encapsulated paramConverter
     * @param ConfigurationInterface $configuration
     *
     * @return ConfigurationInterface
     */
    protected function getProperConfiguration(ConfigurationInterface $configuration)
    {
        $properConfiguration = clone $configuration;

        $properConfiguration->setClass($this->container->getParameter($configuration->getClass()));

        return $properConfiguration;
    }
}
