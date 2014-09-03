<?php

namespace Pim\Bundle\EnrichBundle\Generator;

/**
 * Interface for every PDF generators
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PdfGeneratorInterface
{
    /**
     * Generate a pdf for the given entity
     * @param mixed  $object
     * @param string $format
     * @param array  $context
     *
     * @return string
     */
    public function generate($object, $format, array $context = []);

    /**
     * Test if the given generator support given object and format generation
     * @param mixed $object
     * @param string $format
     *
     * @return boolean
     */
    public function supports($object, $format);
}
