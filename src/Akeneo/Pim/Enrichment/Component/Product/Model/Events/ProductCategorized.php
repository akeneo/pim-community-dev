<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategorized implements ProductEvent
{
    use ProductEventTrait;

    /** @var string */
    private $categoryCode;

    public function __construct(string $categoryCode)
    {
        $this->categoryCode = $categoryCode;
    }

    public function categoryCode(): string
    {
        return $this->categoryCode;
    }
}
