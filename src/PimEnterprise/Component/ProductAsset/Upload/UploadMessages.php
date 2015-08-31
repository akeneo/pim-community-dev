<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

/**
 * Upload messages
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class UploadMessages
{
    /** @var string */
    const STATUS_NEW = 'pimee_product_asset.mass_upload.new';

    /** @var string */
    const STATUS_UPDATED = 'pimee_product_asset.mass_upload.updated';

    /** @var string */
    const STATUS_SCHEDULED = 'pimee_product_asset.mass_upload.scheduled';

    /** @var string */
    const ERROR_CODE = 'pimee_product_asset.mass_upload.error.code';

    /** @var string */
    const ERROR_LOCALE = 'pimee_product_asset.mass_upload.error.locale';

    /** @var string */
    const ERROR_EXISTS = 'pimee_product_asset.mass_upload.error.exists';

    /** @var string */
    const ERROR_CONFLICTS = 'pimee_product_asset.mass_upload.error.conflicts';
}
