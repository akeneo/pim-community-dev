<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Pim\Bundle\ImportExportBundle\Writer\FileWriter;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

/**
 * Product file writer
 *
 * This writer is specialized in writing product file
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFileWriter extends FileWriter
{
    /** @var MediaManager */
    protected $mediaManager;

    /**
     * Constructor
     *
     * @param MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        parent::write(
            array_map(
                function ($item) {
                    return $item['entry'];
                },
                $items
            )
        );

        foreach ($items as $data) {
            foreach ($data['media'] as $media) {
                $this->mediaManager->copy($media, $this->directoryName);
            }
        }
    }
}
