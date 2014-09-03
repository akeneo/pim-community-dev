<?php

namespace Pim\Bundle\EnrichBundle\Generator;

interface PdfGeneratorInterface
{
    /**
     * Generate a pdf for the given entity
     * @param mixed $object
     * @param string $format
     *
     * @return string
     */
    public function generate($object, $format);

    /**
     * Test if the given generator support given object and format generation
     * @param mixed $object
     * @param string $format
     *
     * @return boolean
     */
    public function supports($object, $format);
}
