<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Helper;

use PhpSpec\Exception\Example\SkippingException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FeatureHelper
{
    /**
     * @throws SkippingException
     */
    public static function skipSpecTestWhenPermissionFeatrureIsNotActivated(): void
    {
        if (!\class_exists('Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle')) {
            throw new SkippingException('ReferenceEntity are not available in this scope');
        }
    }
}
