<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

class ParentOfProductAdded implements ProductEvent
{
    use ProductEventTrait;

    /** @var string */
    private $parentModelCode;

    public function __construct(string $parentModelCode)
    {
        $this->parentModelCode = $parentModelCode;
    }

    public function parentModelCode(): string
    {
        return $this->parentModelCode;
    }
}
