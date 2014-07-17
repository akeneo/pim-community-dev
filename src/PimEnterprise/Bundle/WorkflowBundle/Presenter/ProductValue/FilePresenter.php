<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Present a file value
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FilePresenter implements ProductValuePresenterInterface
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
        return 'pim_catalog_file' === $value->getAttribute()->getAttributeType()
            && $value->getData() instanceof AbstractMedia;
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
                '<i class="icon-file"></i><a class="no-hash" href="%s">%s</a>',
                $this->generator->generate('pim_enrich_media_show', ['filename' => $filename]),
                $title
            );
        }
    }
}
