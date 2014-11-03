<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Tab;

/**
 * Checks if a tab is visible because we are in a context of form edition.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TabVisibilityCheckerFormEdit implements TabVisibilityCheckerInterface
{
    /** @var array */
    protected $context;

    /**
     * {@inheritdoc}
     */
    public function setContext(array $context = [])
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible(array $context = [])
    {
        $this->context = array_merge($this->context, $context);

        if (!isset($this->context['form'])) {
            throw new \InvalidArgumentException('The "form" should be provided in the context.');
        }

        return isset($this->context['form']) &&
            null !== $this->context['form']->vars &&
            isset($this->context['form']->vars['value']) &&
            null !== $this->context['form']->vars['value']->getId();
    }
}
