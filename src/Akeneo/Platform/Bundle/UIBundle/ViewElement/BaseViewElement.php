<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\VisibilityCheckerInterface;

/**
 * Basic view element
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseViewElement implements ViewElementInterface
{
    /** @var string */
    protected $alias;

    /** @var string */
    protected $template;

    /** @var array */
    protected $parameters = [];

    /** @var array */
    protected $visibilityCheckers = [];

    /**
     * @param string $alias
     * @param string $template
     * @param array  $parameters
     */
    public function __construct($alias, $template, array $parameters = [])
    {
        $this->alias = $alias;
        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(array $context = [])
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        foreach ($this->visibilityCheckers as $item) {
            list($checker, $config) = $item;
            if (false === $checker->isVisible($config, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addVisibilityChecker(VisibilityCheckerInterface $checker, array $config = [])
    {
        $this->visibilityCheckers[] = [$checker, $config];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibilityCheckers(array $checkers)
    {
        $this->visibilityCheckers = $checkers;

        return $this;
    }
}
