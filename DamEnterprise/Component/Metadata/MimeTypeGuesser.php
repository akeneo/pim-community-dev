<?php

namespace DamEnterprise\Component\Metadata;

//TODO: delete it, we use the symfony's one
//TODO: the list of mimetypes is defined here
// vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/File/MimeType/MimeTypeExtensionGuesser.php
class MimeTypeGuesser
{
    public static function guess(\SplFileInfo $file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        return $type;
    }
}
