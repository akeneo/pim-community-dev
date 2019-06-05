<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeUrlPrefix
{
    /** @var string */
    private $prefix;

    private function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public static function fromString(?string $prefix): self
    {
        Assert::nullOrStringNotEmpty($prefix, 'The prefix cannot be empty');

        return new self($prefix);
    }

    public function normalize(): string
    {
        return $this->prefix;
    }
}
