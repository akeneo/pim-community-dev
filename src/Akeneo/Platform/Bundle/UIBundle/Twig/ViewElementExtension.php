<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementInterface;
use Akeneo\Platform\Bundle\UIBundle\ViewElement\ViewElementRegistry;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Twig extension to display view elements
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewElementExtension extends \Twig_Extension
{
    /** @var ViewElementRegistry */
    protected $registry;

    /** @var EngineInterface */
    protected $templating;

    /** @var bool */
    protected $debug;

    /**
     * @param ViewElementRegistry $registry
     * @param EngineInterface     $templating
     * @param bool                $debug
     */
    public function __construct(ViewElementRegistry $registry, EngineInterface $templating, bool $debug = false)
    {
        $this->registry = $registry;
        $this->templating = $templating;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction(
                'view_elements',
                fn(array $context, $type) => $this->renderViewElements($context, $type),
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'view_element_aliases',
                fn(array $context, $type) => $this->getViewElementAliases($context, $type),
                ['needs_context' => true, 'is_safe' => ['html']]
            )
        ];
    }

    /**
     * Render view elements
     *
     * @param array  $context
     * @param string $type
     */
    public function renderViewElements(array $context, string $type): string
    {
        $elements = $this->getViewElements($type, $context);
        $content = '';

        $elementCount = count($elements);
        for ($i = 0; $i < $elementCount; $i++) {
            $element = $elements[$i];
            $elementContext = [
                'viewElement' => [
                    'alias' => $element->getAlias(),
                    'loop'  => [
                        'index'  => $i + 1,
                        'first'  => 0 === $i,
                        'last'   => $elementCount === $i + 1,
                        'length' => $elementCount
                    ]
                ]
            ] + $context;

            if ($this->debug) {
                $content .= sprintf("<!-- Start view element template: %s -->\n", $element->getTemplate());
            }

            $content .= $this->templating->render(
                $element->getTemplate(),
                array_replace_recursive($elementContext, $element->getParameters($context))
            );

            if ($this->debug) {
                $content .= sprintf("<!-- End view element template: %s -->\n", $element->getTemplate());
            }
        }

        return $content;
    }

    /**
     * Return a list of aliases of displayable view elements of the requested type
     *
     * @param array  $context
     * @param string $type
     *
     * @return string[]
     */
    public function getViewElementAliases(array $context, string $type): array
    {
        $elements = $this->getViewElements($type, $context);
        $result = [];

        foreach ($elements as $element) {
            $result[] = $element->getAlias();
        }

        return $result;
    }

    /**
     * Returns view elements from the registry that are visible in the given context
     *
     * @param string $type
     * @param array  $context
     *
     * @return ViewElementInterface[]
     */
    protected function getViewElements(string $type, array $context = []): array
    {
        $elements = $this->registry->get($type);
        $result = [];

        foreach ($elements as $element) {
            if (!$element->isVisible($context)) {
                continue;
            }

            $result[] = $element;
        }

        return $result;
    }
}
