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

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAttributeDetails extends AbstractAttributeDetails
{
    public const ATTRIBUTE_TYPE = 'text';
    public const MAX_LENGTH = 'max_length';
    public const IS_TEXT_AREA = 'is_textarea';
    public const IS_RICH_TEXT_EDITOR = 'is_rich_text_editor';
    public const VALIDATION_RULE = 'validation_rule';
    public const REGULAR_EXPRESSION = 'regular_expression';

    /** @var AttributeMaxLength */
    public $maxLength;

    /** @var AttributeIsTextarea */
    public $isTextarea;

    /** @var AttributeIsRichTextEditor */
    public $isRichTextEditor;

    /** @var AttributeValidationRule */
    public $validationRule;

    /** @var AttributeRegularExpression */
    public $regularExpression;

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                self::MAX_LENGTH          => $this->maxLength->normalize(),
                self::TYPE                => self::ATTRIBUTE_TYPE,
                self::IS_TEXT_AREA        => $this->isTextarea->normalize(),
                self::IS_RICH_TEXT_EDITOR => $this->isRichTextEditor->normalize(),
                self::VALIDATION_RULE     => $this->validationRule->normalize(),
                self::REGULAR_EXPRESSION  => $this->regularExpression->normalize(),
            ]
        );
    }
}
