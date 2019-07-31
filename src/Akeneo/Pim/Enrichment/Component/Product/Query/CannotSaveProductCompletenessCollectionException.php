<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CannotSaveProductCompletenessCollectionException extends \RuntimeException
{
    public function __construct(int $productId, $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Cannot save product completeness collection for product id $productId", $code, $previous);
    }
}
