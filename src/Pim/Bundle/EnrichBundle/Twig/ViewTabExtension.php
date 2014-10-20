<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Pim\Bundle\EnrichBundle\ViewElement\ViewElementsRegistry;

/**
 * Twig extension to display tabs and manage tab registration
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewTabExtension extends \Twig_Extension
{
    /**
     * @param ViewElementsRegistry $viewRegistry
     */
    public function __construct(ViewElementsRegistry $viewRegistry)
    {
        $this->viewRegistry = $viewRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'tab_titles'         => new \Twig_Function_Method(
                $this,
                'getTabTitles',
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            'tab_contents' => new \Twig_Function_Method(
                $this,
                'getTabContents',
                ['needs_context' => true, 'is_safe' => ['html']]
            )
        ];
    }

    /**
     * Get tabs titles
     * @param array  $context
     * @param string $identifier
     *
     * @return string
     */
    public function getTabTitles(array $context, $identifier)
    {
        $views = $this->viewRegistry->getViews('tab', $identifier);

        $tabs = [];

        foreach ($views as $view) {
            if ($view->isVisible($context)) {
                $tabs[] = $view->getTitle($context);
            }
        }

        return $tabs;
    }

    /**
     * Get content of tabs
     * @param array  $context
     * @param string $identifier
     *
     * @return string
     */
    public function getTabContents(array $context, $identifier)
    {
        $views = $this->viewRegistry->getViews('tab', $identifier);

        $content  = '';
        $firstTab = true;

        foreach ($views as $view) {
            if ($view->isVisible($context)) {
                $context = array_merge(
                    $context,
                    [
                        'tab_id'    => str_replace('.', '-', $view->getTitle()),
                        'first_tab' => $firstTab
                    ]
                );

                $content .= $view->getContent($context);
                $firstTab = false;
            }
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_tab_extension';
    }
}
