<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Tab;

/**
 * Render a template only if we are in a edit form scenario
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnFormEditTab implements TabInterface
{
    /** @var TabInterface */
    protected $tab;

    /**
     * @param TabInterface $tab
     */
    public function __construct(TabInterface $tab) {
        $this->tab = $tab;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(array $context = [])
    {
        return $this->tab->getContent($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(array $context = [])
    {
        return $this->tab->getTitle($context);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        return isset($context['form']) &&
            null !== $context['form']->vars &&
            isset($context['form']->vars['value']) &&
            null !== $context['form']->vars['value']->getId() &&
            $this->tab->isVisible($context);
    }
}
