<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Asset event
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class AssetEvent extends GenericEvent
{
    /** @var string Event triggered after upload of assets files */
    const POST_UPLOAD_FILES = 'pimee_product_asset.asset.files_upload.post';

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
