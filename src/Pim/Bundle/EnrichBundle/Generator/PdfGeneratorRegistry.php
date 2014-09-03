<?php

namespace Pim\Bundle\EnrichBundle\Generator;

/**
 * Sort helper defines a set of static methods to reorder your arrays
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PdfGeneratorRegistry
{
    /**
     * @var array
     */
    protected $generators = [];

    /**
     * Add a generator to the registry
     * @param PdfGeneratorInterface $generator
     */
    public function addGenerator(PdfGeneratorInterface $generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * Generate a pdf with the right generator
     * @param mixed $object
     * @param string $format
     *
     * @return string
     */
    public function generate($object, $format)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($object, $format)) {
                return $generator->generate($object, $format);
            }
        }

        return '';
    }
}
