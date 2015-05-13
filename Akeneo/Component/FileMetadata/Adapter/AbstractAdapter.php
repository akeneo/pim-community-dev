<?php

namespace Akeneo\Component\FileMetadata\Adapter;

/**
 * Basic implementation of an Adapter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractAdapter implements AdapterInterface
{
    //TODO: the list of mimetypes is defined here
    // vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/File/MimeType/MimeTypeExtensionGuesser.php

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
