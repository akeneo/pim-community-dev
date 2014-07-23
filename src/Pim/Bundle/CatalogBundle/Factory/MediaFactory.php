<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;

/**
 * A Media object factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFactory
{
    /**
     * @param File|null $file
     *
     * @return Media
     */
    public function createMedia(File $file = null)
    {
        $media = new Media();
        $media->setFile($file);

        return $media;
    }
}
