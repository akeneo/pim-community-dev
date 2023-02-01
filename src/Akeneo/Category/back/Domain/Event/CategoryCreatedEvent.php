<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Event;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryCreatedEvent
{
    public function __construct(private string $categoryCode)
    {
    }

    public function getCategoryCode(): string
    {
        return $this->categoryCode;
    }
}
