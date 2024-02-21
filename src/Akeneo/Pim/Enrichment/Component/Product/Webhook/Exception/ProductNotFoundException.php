<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNotFoundException extends \RuntimeException implements EventBuildingExceptionInterface
{
    public function __construct(UuidInterface $uuid)
    {
        parent::__construct(sprintf('Product "%s" not found', $uuid->toString()));
    }
}
