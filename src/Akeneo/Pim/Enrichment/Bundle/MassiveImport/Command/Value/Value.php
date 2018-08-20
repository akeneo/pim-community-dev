<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\MassiveImport\Command\Value;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Value
{
    /** @var string */
    private $attributeCode;

    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /** @var mixed */
    private $data;

    public function __construct(string $attributeCode, ?string $localeCode, ?string $channelCode, $data)
    {
        $this->attributeCode = $attributeCode;
        $this->channelCode = $channelCode;
        $this->localeCode = $localeCode;
        $this->data = $data;
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

    public function data()
    {
        return $this->data;
    }
}
