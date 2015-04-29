<?php


namespace DamEnterprise\Component\Metadata\Adapter;


abstract class AbstractAdapter implements AdapterInterface
{
    //TODO: the list of mimetypes is defined here
    // vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/File/MimeType/MimeTypeExtensionGuesser.php

    /** @var array */
    protected $mimeTypes;

    public function supportsMimeType($mimeType)
    {
        return in_array($mimeType, $this->mimeTypes);
    }

    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }
}
