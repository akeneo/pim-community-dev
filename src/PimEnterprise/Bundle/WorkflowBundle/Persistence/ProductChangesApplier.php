<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesApplier
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function apply(AbstractProduct $product, array $changes)
    {
        $changes = $this->prepareChanges($changes);
        $this
            ->formFactory
            ->createBuilder('form', $product)
            ->add(
                'values',
                'pim_enrich_localized_collection',
                [
                    'type' => 'pim_product_value',
                    'allow_add' => false,
                    'allow_delete' => false,
                    'by_reference' => false,
                    'cascade_validation' => true,
                    'currentLocale' => null,
                    'comparisonLocale' => null,
                ]
            )
            ->getForm()
            ->submit($changes, false);
    }

    protected function prepareChanges(array $changes)
    {
        if (!isset($changes['values'])) {
            return $changes;
        }

        foreach ($changes['values'] as $key => $change) {
            if (isset($change['media']) && $this->isUploadingMedia($change['media'])) {
                $changes['values'][$key]['media'] = [
                    'file' => new UploadedFile(
                        $change['media']['filePath'],
                        $change['media']['originalFilename'],
                        $change['media']['mimeType'],
                        $change['media']['size']
                    )
                ];
            }
        }

        return $changes;
    }

    /**
     * Wether or not media data should be converted into uploaded file
     *
     * @param array $media
     *
     * @return boolean
     */
    protected function isUploadingMedia(array $media)
    {
        foreach (['filePath', 'originalFilename', 'mimeType', 'size'] as $mediaKey) {
            if (!array_key_exists($mediaKey, $media)) {
                return false;
            }
        }

        return true;
    }
}
