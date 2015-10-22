<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Component\Catalog\FileStorage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product template media manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateMediaManager
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /**
     * @param FileStorerInterface $fileStorer
     * @param NormalizerInterface    $normalizer
     */
    public function __construct(FileStorerInterface $fileStorer, NormalizerInterface $normalizer)
    {
        $this->fileStorer = $fileStorer;
        $this->normalizer = $normalizer;
    }

    /**
     * Handles the media of the given product template
     *
     * @param ProductTemplateInterface $template
     */
    public function handleProductTemplateMedia(ProductTemplateInterface $template)
    {
        $mediaHandled = false;
        foreach ($template->getValues() as $value) {
            if (null !== $value->getMedia() && true === $value->getMedia()->isRemoved()) {
                $mediaHandled = true;
                $value->setMedia(null);
            } elseif (null !== $value->getMedia() && null !== $value->getMedia()->getUploadedFile()) {
                $mediaHandled = true;
                $file = $this->fileStorer->store(
                    $value->getMedia()->getUploadedFile(),
                    FileStorage::CATALOG_STORAGE_ALIAS,
                    true
                );
                $value->setMedia($file);
            }
        }

        if ($mediaHandled) {
            $this->updateNormalizedValues($template);
        }
    }

    /**
     * Updates normalized product template values (required after handling new media added to a template)
     *
     * @param ProductTemplateInterface $template
     */
    protected function updateNormalizedValues(ProductTemplateInterface $template)
    {
        $valuesData = $this->normalizer->normalize($template->getValues(), 'json', ['entity' => 'product']);
        $template->setValuesData($valuesData);
    }
}
