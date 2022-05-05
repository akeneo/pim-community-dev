<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsRichTextEditor
{
    private function __construct(
        private bool $isRichTextEditor
    ) {
    }

    public static function fromBoolean(bool $isRichTextEditor): self
    {
        return new self($isRichTextEditor);
    }

    public function isYes(): bool
    {
        return $this->isRichTextEditor;
    }

    public function normalize(): bool
    {
        return $this->isRichTextEditor;
    }
}
