<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class ProductTitle
{
    /** @var string */
    private $title;

    public function __construct(string $title)
    {
        $title = trim($title);

        if (empty($title)) {
            throw new \InvalidArgumentException('A title must not be empty');
        }

        $this->title = $title;
    }

    public function __toString()
    {
        return $this->title;
    }
}
