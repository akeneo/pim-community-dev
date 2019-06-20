<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Query;

/**
 * Data transfer objects that holds informations about an asset family asset.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetInformation
{
    /** @var string */
    public $assetFamilyIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels = [];
}
