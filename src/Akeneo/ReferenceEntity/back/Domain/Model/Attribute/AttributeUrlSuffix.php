<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeUrlSuffix
{
    /** @var string */
    private $suffix;

    private function __construct(string $suffix)
    {
        $this->suffix = $suffix;
    }

    public static function fromString(?string $suffix): self
    {
        Assert::nullOrStringNotEmpty($suffix, 'The suffix cannot be empty');

        return new self($suffix);
    }

    public function normalize(): string
    {
        return $this->suffix;
    }
}
