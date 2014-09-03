<?php

namespace Pim\Bundle\EnrichBundle\Generator;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;

/**
 * PDF Generator used to generate PDF for a Product
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfGenerator implements PdfGeneratorInterface
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $template;

    /**
     * @param EngineInterface $templating
     * @param string          $template
     */
    public function __construct(EngineInterface $templating, $template)
    {
        $this->templating = $templating;
        $this->template   = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($object, $format, array $context = [])
    {
        $params = array_merge(
            $context,
            [
                'product'        => $object,
                'generationDate' => $this->getGenerationDate()
            ]
        );

        return $this->templating->render($this->template, $params);
    }

    protected function getGenerationDate()
    {
        return new \DateTime('now');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, $format)
    {
        return $object instanceof AbstractProduct;
    }
}
