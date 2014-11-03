<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

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

    /**
     * @param string $alias
     * @param string $template
     */
    public function __construct($alias, $template)
    {
        $this->alias    = $alias;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(array $context = [])
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext(array $context = [])
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(array $context = [])
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(array $context = [])
    {
        return [];
    }
}
