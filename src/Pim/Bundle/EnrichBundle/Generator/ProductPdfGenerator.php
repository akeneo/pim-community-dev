<?php

namespace Pim\Bundle\EnrichBundle\Generator;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Sort helper defines a set of static methods to reorder your arrays
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfGenerator implements PdfGeneratorInterface
{
    /**
     * Templating engine
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $format)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $format)
    {
        return true;
    }
}
