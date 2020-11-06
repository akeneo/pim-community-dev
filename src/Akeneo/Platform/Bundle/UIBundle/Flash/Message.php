<?php

namespace Akeneo\Platform\Bundle\UIBundle\Flash;

/**
 * A flash message
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Message
{
    /** @var string */
    protected $template;

    /** @var array */
    protected $parameters;

    /**
     * @param string $template
     * @param array  $parameters
     */
    public function __construct(string $template, array $parameters = [])
    {
        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * Set the template
     *
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * Get the template
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Set the parameters
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Get the parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
