<?php

namespace Pim\Bundle\EnrichBundle\Generator;

/**
 * Registry used to generate PDF using registered generators
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
     * @param mixed  $object
     * @param string $format
     * @param array  $context
     *
     * @return string
     */
    public function generate($object, $format, $context)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($object, $format)) {
                return $generator->generate($object, $format, $context);
            }
        }

        return '';
    }
}
