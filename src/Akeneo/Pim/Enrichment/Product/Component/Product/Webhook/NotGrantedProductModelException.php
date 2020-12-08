<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Component\Product\Webhook;

use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;
use Throwable;

class NotGrantedProductModelException extends \Exception implements EventBuildingExceptionInterface
{
    public function __construct(
        string $connectionCode,
        string $productModelCode,
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'The user "%s" does not have "view" permission access to the product model "%s".',
                $connectionCode,
                $productModelCode
            ),
            $code,
            $previous
        );
    }
}
