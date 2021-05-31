<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateTextAttributeCommand extends AbstractCreateAttributeCommand
{
    public ?int $maxLength = null;

    public bool $isTextarea;

    public bool $isRichTextEditor;

    public ?string $validationRule = null;

    public ?string $regularExpression = null;

    public function __construct(
        string $assetFamilyIdentifier,
        string $code,
        array $labels,
        bool $isRequired,
        bool $isReadOnly,
        bool $valuePerChannel,
        bool $valuePerLocale,
        ?int $maxLength,
        bool $isTextarea,
        bool $isRichTextEditor,
        ?string $validationRule,
        ?string $regularExpression
    ) {
        parent::__construct(
            $assetFamilyIdentifier,
            $code,
            $labels,
            $isRequired,
            $isReadOnly,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->maxLength = $maxLength;
        $this->isTextarea = $isTextarea;
        $this->isRichTextEditor = $isRichTextEditor;
        $this->validationRule = $validationRule;
        $this->regularExpression = $regularExpression;
    }
}
