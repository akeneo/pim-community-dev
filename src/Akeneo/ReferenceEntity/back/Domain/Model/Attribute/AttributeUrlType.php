<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeUrlType
{
    private const EXISTING_TYPES = [
        'image',
        'others'
    ];

    /** @var string */
    private $type;

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function fromString(string $type): self
    {
        Assert::notEmpty($type, 'The URL type cannot be empty');
        Assert::oneOf($type, self::EXISTING_TYPES, 'This URL type is not allowed');

        return new self($type);
    }

    public function normalize(): string
    {
        return $this->type;
    }
}
