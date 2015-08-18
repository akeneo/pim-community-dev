<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileMetadata\Adapter;

/**
 * Basic implementation of an Adapter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * @see the list of mimetypes is defined here
 *      vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/File/MimeType/MimeTypeExtensionGuesser.php
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /** @var array */
    protected $mimeTypes;

    /**
     * {@inheritdoc}
     */
    public function isMimeTypeSupported($mimeType)
    {
        return in_array($mimeType, $this->mimeTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedMimeTypes()
    {
        return $this->mimeTypes;
    }
}
