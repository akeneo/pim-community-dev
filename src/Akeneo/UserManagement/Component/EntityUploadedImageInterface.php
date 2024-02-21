<?php

namespace Akeneo\UserManagement\Component;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface EntityUploadedImageInterface
{
    /**
     * Set image name.
     *
     * @param  string $image
     * @return object
     */
    public function setImage($image);

    /**
     * Get image name.
     *
     * @return object
     */
    public function getImage();

    /**
     * Unset image file.
     *
     * @return object
     */
    public function unsetImageFile();

    /**
     * Get uploaded file.
     *
     * @return UploadedFile
     */
    public function getImageFile();

    /**
     * Get upload dir.
     *
     * @return string
     */
    public function getUploadDir();
}
