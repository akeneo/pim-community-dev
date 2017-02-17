<?php

namespace Pim\Component\Catalog\Manager;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
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

    /** @var ProductValueFactory */
    protected $productValueFactory;

    /**
     * @param FileStorerInterface $fileStorer
     * @param NormalizerInterface $normalizer
     * @param ProductValueFactory $productValueFactory
     */
    public function __construct(
        FileStorerInterface $fileStorer,
        NormalizerInterface $normalizer,
        ProductValueFactory $productValueFactory
    ) {
        $this->fileStorer = $fileStorer;
        $this->normalizer = $normalizer;
        $this->productValueFactory = $productValueFactory;
    }

    /**
     * Handles the media of the given product template
     *
     * @param ProductTemplateInterface $template
     */
    public function handleProductTemplateMedia(ProductTemplateInterface $template)
    {
        $mediaHandled = false;
        $newValues = [];

        foreach ($template->getValues() as $value) {
            if (null !== $value->getMedia() && true === $value->getMedia()->isRemoved()) {
                $mediaHandled = true;
                $newValues[] = $this->productValueFactory->create(
                    $value->getAttribute(),
                    $value->getScope(),
                    $value->getLocale(),
                    null
                );
            } elseif (null !== $value->getMedia() && null !== $value->getMedia()->getUploadedFile()) {
                $mediaHandled = true;
                $file = $this->fileStorer->store(
                    $value->getMedia()->getUploadedFile(),
                    FileStorage::CATALOG_STORAGE_ALIAS,
                    true
                );
                $newValues[] = $this->productValueFactory->create(
                    $value->getAttribute(),
                    $value->getScope(),
                    $value->getLocale(),
                    $file->getKey()
                );
            }
        }

        if ($mediaHandled) {
            $this->updateNormalizedValues($template, $newValues);
        }
    }

    /**
     * Updates normalized product template values (required after handling new media added to a template)
     *
     * @param ProductTemplateInterface $template
     * @param ProductValueInterface[]  $newValues
     */
    protected function updateNormalizedValues(ProductTemplateInterface $template, array $newValues)
    {
        $valuesData = $this->normalizer->normalize($newValues, 'standard', ['entity' => 'product']);
        $template->setValuesData($valuesData);
    }
}
