<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Present images side by side
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ImagePresenter extends FilePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports($data)
    {
        return $data instanceof ValueInterface &&
            AttributeTypes::IMAGE === $data->getAttribute()->getType();
    }

    /**
     * Create a file element
     *
     * @param string $fileKey
     * @param string $originalFilename
     *
     * @return string
     */
    protected function createFileElement($fileKey, $originalFilename)
    {
        return sprintf(
            '<img src="%s" title="%s" />',
            $this->generator->generate(
                'pim_enrich_media_show',
                [
                    'filename' => urlencode($fileKey),
                    'filter'   => 'thumbnail',
                ]
            ),
            $originalFilename
        );
    }
}
