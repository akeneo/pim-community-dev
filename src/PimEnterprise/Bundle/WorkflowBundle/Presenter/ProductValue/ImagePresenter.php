<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Present an image value
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ImagePresenter implements ProductValuePresenterInterface
{
    /** @var UrlGeneratorInterface */
    protected $generator;

    /**
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $value)
    {
        return 'pim_catalog_image' === $value->getAttribute()->getAttributeType()
            && $value->getData() instanceof AbstractMedia
            && 0 === strpos($value->getData()->getMimeType(), 'image');
    }

    /**
     * {@inheritdoc}
     */
    public function present(ProductValueInterface $value)
    {
        $filename = $value->getData()->getFilename();
        $title = $value->getData()->getOriginalFilename();

        if (null !== $filename && null !== $title) {
            return sprintf(
                '<img src="%s" title="%s" />',
                $this->generator->generate(
                    'pim_enrich_media_show',
                    [
                        'filename' => $filename,
                        'filter' => 'thumbnail',
                    ]
                ),
                $title
            );
        }
    }
}
