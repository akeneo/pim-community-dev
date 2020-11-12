<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use Throwable;

class NotGrantedProductException extends \Exception implements EventBuildingExceptionInterface
{
    public function __construct(
        string $connectionCode,
        string $productIdentifier,
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'The user "%s" does not have "view" permission access to the product "%s".',
                $connectionCode,
                $productIdentifier
            ),
            $code,
            $previous
        );
    }
}
