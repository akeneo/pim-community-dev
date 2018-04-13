<?php

namespace Pim\Component\User;

use Pim\Component\Catalog\Model\CategoryInterface;
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

    /**
     * {@inheritdoc}
     */
    public function getAssetDelayReminder();

    /**
     * {@inheritdoc}
     */
    public function setAssetDelayReminder($assetDelayReminder);

    /**
     * {@inheritdoc}
     */
    public function getDefaultAssetTree();

    /**
     * {@inheritdoc}
     */
    public function setDefaultAssetTree(CategoryInterface $defaultAssetTree);

    /**
     * {@inheritdoc}
     */
    public function hasProposalsToReviewNotification();

    /**
     * {@inheritdoc}
     */
    public function setProposalsToReviewNotification($proposalsToReviewNotification);

    /**
     * {@inheritdoc}
     */
    public function hasProposalsStateNotification();

    /**
     * {@inheritdoc}
     */
    public function setProposalsStateNotification($proposalsStateNotification);
}
