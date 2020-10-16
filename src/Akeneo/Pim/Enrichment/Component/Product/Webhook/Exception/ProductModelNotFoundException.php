<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNotFoundException extends \RuntimeException implements EventBuildingExceptionInterface
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Product Model "%s" not found', $identifier));
    }
}
