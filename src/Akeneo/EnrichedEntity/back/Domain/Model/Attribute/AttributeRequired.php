<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequired
{
    /** @var bool */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromBoolean(bool $isRequired): self
    {
        return new self($isRequired);
    }

    public function isYes(): bool
    {
        return $this->value;
    }
}
