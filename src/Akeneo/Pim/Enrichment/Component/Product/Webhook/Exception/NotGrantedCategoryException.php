<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception;

use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotGrantedCategoryException extends \Exception implements EventBuildingExceptionInterface
{
    public function __construct(
        string $message,
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            0,
            $previous
        );
    }
}
