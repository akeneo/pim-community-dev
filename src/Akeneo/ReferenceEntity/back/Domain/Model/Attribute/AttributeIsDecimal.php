<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsDecimal
{
    /** @var bool */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromBoolean(bool $isDecimal): self
    {
        return new self($isDecimal);
    }

    public function normalize(): bool
    {
        return $this->value;
    }
}
