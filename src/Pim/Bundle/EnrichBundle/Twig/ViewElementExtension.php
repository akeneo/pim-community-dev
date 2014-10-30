<?php

namespace Pim\Bundle\EnrichBundle\Twig;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\ViewElement\ProtectedViewElementInterface;
use Pim\Bundle\EnrichBundle\ViewElement\ViewElementRegistry;
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

    /** @var SecurityFacade */
    protected $security;

    /** @var EngineInterface */
    protected $templating;

    /**
     * @param ViewElementRegistry $registry
     * @param SecurityFacade      $security
     * @param EngineInterface     $templating
     */
    public function __construct(ViewElementRegistry $registry, SecurityFacade $security, EngineInterface $templating)
    {
        $this->registry   = $registry;
        $this->security   = $security;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'view_elements' => new \Twig_Function_Method(
                $this,
                'renderViewElements',
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            'view_element_aliases' => new \Twig_Function_Method(
                $this,
                'getViewElementAliases',
                ['needs_context' => true, 'is_safe' => ['html']]
            )
        ];
    }

    /**
     * Render view elements
     * @param array  $context
     * @param string $type
     *
     * @return string
     */
    public function renderViewElements(array $context, $type)
    {
        $elements = $this->getViewElements($type, $context);
        $content  = '';

        $elementCount = count($elements);
        for ($i = 0; $i < $elementCount; $i++) {
            $element = $elements[$i];
            $context = array_merge(
                $context,
                [
                    'viewElement' => [
                        'alias' => $element->getAlias(),
                        'loop' => [
                            'index'  => $i + 1,
                            'first'  => 0 === $i,
                            'last'   => $elementCount === $i + 1,
                            'length' => $elementCount
                        ]
                    ]
                ]
            );

            $content .= $this->templating->render(
                $element->getTemplate(),
                array_merge($context, $element->getParameters())
            );
        }

        return $content;
    }

    /**
     * Return a list of aliases of displayable view elements of the requested type
     * @param array  $context
     * @param string $type
     *
     * @return string[]
     */
    public function getViewElementAliases(array $context, $type)
    {
        $elements = $this->getViewElements($type, $context);
        $result = [];

        foreach ($elements as $element) {
            $result[] = $element->getAlias($context);
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
    protected function getViewElements($type, array $context = [])
    {
        $elements = $this->registry->get($type);
        $result = [];

        foreach ($elements as $element) {
            if ($element->supportsContext($context)) {
                if ($element instanceof ProtectedViewElementInterface) {
                    if (!$element->isVisible($context)) {
                        continue;
                    }
                    $aclResources = $element->getAclResources($context);
                    $granted = true;
                    foreach ($aclResources as $resource) {
                        $resource = is_array($resource) ? $resource : [$resource];
                        $granted = call_user_func_array([$this->security, 'isGranted'], $resource);
                        if (!$granted) {
                            break;
                        }
                    }
                    if (!$granted) {
                        continue;
                    }
                }

                $result[] = $element;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_view_element_extension';
    }
}
