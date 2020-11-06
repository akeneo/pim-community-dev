<?php

namespace Akeneo\UserManagement\Component;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface EntityUploadedImageInterface
{
    /**
     * Set image name.
     *
     * @param  string $image
     */
    public function setImage(string $image): object;

    /**
     * Get image name.
     */
    public function getImage(): object;

    /**
     * Unset image file.
     */
    public function unsetImageFile(): object;

    /**
     * Get uploaded file.
     */
    public function getImageFile(): UploadedFile;

    /**
     * Get upload dir.
     */
    public function getUploadDir(): string;
}
