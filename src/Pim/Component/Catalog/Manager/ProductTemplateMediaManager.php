<?php

namespace Pim\Component\Catalog\Manager;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\ProductValue\MediaProductValueInterface;

/**
 * Product template media manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateMediaManager
{
    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var ProductValueFactory */
    protected $productValueFactory;

    /**
     * @param FileStorerInterface $fileStorer
     * @param ProductValueFactory $productValueFactory
     */
    public function __construct(
        FileStorerInterface $fileStorer,
        ProductValueFactory $productValueFactory
    ) {
        $this->fileStorer = $fileStorer;
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
        $values = $template->getValues();

        foreach ($values as $value) {
            if ($value instanceof MediaProductValueInterface) {
                if (null !== $value->getData() && true === $value->getData()->isRemoved()) {
                    $mediaHandled = true;

                    $values->remove($value);
                    $values->add(
                        $this->productValueFactory->create(
                            $value->getAttribute(),
                            $value->getScope(),
                            $value->getLocale(),
                            null
                        )
                    );
                } elseif (null !== $value->getData() && null !== $value->getData()->getUploadedFile()) {
                    $mediaHandled = true;

                    $file = $this->fileStorer->store(
                        $value->getData()->getUploadedFile(),
                        FileStorage::CATALOG_STORAGE_ALIAS,
                        true
                    );

                    $values->remove($value);
                    $values->add(
                        $this->productValueFactory->create(
                            $value->getAttribute(),
                            $value->getScope(),
                            $value->getLocale(),
                            $file->getKey()
                        )
                    );
                }
            }
        }

        if ($mediaHandled) {
            $template->setValues($values);
        }
    }
}
