<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Exception\RendererRequiredException;

/**
 * Registry used to render an item using registered renderers
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RendererRegistry
{
    /** @var RendererInterface[] */
    protected $renderers = [];

    /**
     * Add a renderer to the registry
     *
     * @param RendererInterface $renderer
     */
    public function addRenderer(RendererInterface $renderer)
    {
        $this->renderers[] = $renderer;
    }

    /**
     * Render an item with the right renderer
     *
     * @param mixed  $object
     * @param string $format
     * @param array  $context
     *
     * @throws RendererRequiredException
     *
     * @return string
     */
    public function render($object, $format, $context)
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($object, $format)) {
                return $renderer->render($object, $format, $context);
            }
        }

        throw new RendererRequiredException(
            sprintf('At least one renderer should be registered to render the object : %s', get_class($object))
        );
    }
}
