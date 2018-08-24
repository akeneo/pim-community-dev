<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsTextArea
{
    /** @var bool */
    private $isTextArea;

    private function __construct(bool $isRichTextEditor)
    {
        $this->isTextArea = $isRichTextEditor;
    }

    public static function fromBoolean(bool $isRichTextEditor): self
    {
        return new self($isRichTextEditor);
    }

    public function isYes(): bool
    {
        return $this->isTextArea;
    }

    public function normalize(): bool
    {
        return $this->isTextArea;
    }
}
