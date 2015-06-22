<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

final class AssetEvent extends GenericEvent
{
    /** @var string Event trigerred afer assets files have been uploaded */
    const FILES_UPLOAD_POST = 'pimee_product_asset.asset.files_upload.post';

    /**
     * @param AssetInterface $subject
     * @param array          $arguments
     */
    public function __construct(AssetInterface $subject = null, array $arguments = [])
    {
        parent::__construct($subject, $arguments);
    }

    /**
     * @return AssetInterface
     */
    public function getSubject()
    {
        return parent::getSubject();
    }
}
