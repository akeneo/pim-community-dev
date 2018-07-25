<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer;

/**
 * Interface for every renderers
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RendererInterface
{
    /**
     * Render the given entity in the specified format
     *
     * @param mixed  $object
     * @param string $format
     * @param array  $context
     *
     * @return mixed
     */
    public function render($object, $format, array $context = []);

    /**
     * Test if the given generator support given object and format rendering
     *
     * @param mixed  $object
     * @param string $format
     *
     * @return bool
     */
    public function supports($object, $format);
}
