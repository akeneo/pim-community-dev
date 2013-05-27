<?php

namespace Oro\Bundle\GridBundle\Property;

interface TwigPropertyInterface extends PropertyInterface
{
    /**
     * @param \Twig_Environment $environment
     */
    public function setEnvironment(\Twig_Environment $environment);
}
