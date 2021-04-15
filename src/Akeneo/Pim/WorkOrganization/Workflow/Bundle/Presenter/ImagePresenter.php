<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;

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
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return $attributeType === AttributeTypes::IMAGE;
    }

    /**
     * Create a file element
     *
     * @param string $fileKey
     * @param string $originalFilename
     *
     * @return array
     */
    protected function createFileElement($fileKey, $originalFilename)
    {
        return [
            'fileKey' => urlencode($fileKey),
            'originalFileName' => $originalFilename,
        ];
    }
}
