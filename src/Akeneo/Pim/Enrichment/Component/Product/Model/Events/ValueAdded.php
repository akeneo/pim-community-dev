<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueAdded
{
    /** @var null|string */
    private $productIdentifier;

    /** @var string */
    private $attributeCode;

    /** @var string */
    private $localeCode;

    /** @var string */
    private $channelCode;

    public function __construct(?string $productIdentifier, string $attributeCode, ?string $localeCode, ?string $channelCode)
    {
        $this->productIdentifier = $productIdentifier;
        $this->attributeCode = $attributeCode;
        $this->localeCode = $localeCode;
        $this->channelCode = $channelCode;
    }

    public function productIdentifier(): ?string
    {
        return $this->productIdentifier;
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }
}
