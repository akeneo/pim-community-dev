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

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAttribute extends AbstractAttribute
{
    private const ATTRIBUTE_TYPE = 'text';

    /** @var AttributeMaxLength */
    private $maxLength;

    /** @var AttributeIsTextArea */
    private $isTextArea;

    /** @var AttributeValidationRule */
    private $validationRule;

    /** @var AttributeRegularExpression */
    private $regularExpression;

    /** @var AttributeIsRichTextEditor */
    private $isRichTextEditor;

    /**
     * TextAttribute constructor.
     *
     * @param AttributeIdentifier        $identifier
     * @param EnrichedEntityIdentifier   $enrichedEntityIdentifier
     * @param AttributeCode              $code
     * @param LabelCollection            $labelCollection
     * @param AttributeOrder             $order
     * @param AttributeIsRequired        $isRequired
     * @param AttributeValuePerChannel   $valuePerChannel
     * @param AttributeValuePerLocale    $valuePerLocale
     * @param AttributeMaxLength         $maxLength
     * @param AttributeIsTextArea        $isTextArea
     * @param AttributeValidationRule    $validationRule
     * @param AttributeRegularExpression $regularExpression
     * @param AttributeIsRichTextEditor  $isRichTextEditor
     */
    protected function __construct(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxLength $maxLength,
        AttributeIsTextArea $isTextArea,
        AttributeValidationRule $validationRule,
        AttributeRegularExpression $regularExpression,
        AttributeIsRichTextEditor $isRichTextEditor
    ) {
        if ($isTextArea->isYes()) {
            Assert::true(
                $validationRule->isNone() && $regularExpression->isNone(),
                'It is not possible to create a text area attribute with a validation rule.'
            );
        } else {
            Assert::false($isRichTextEditor->isYes());
            if ($validationRule->isRegex()) {
                Assert::false(
                    $regularExpression->isNone(),
                    'It is not possible to create a text attribute with a regular expression without specifying it'
                );
            }
        }
        parent::__construct(
            $identifier,
            $enrichedEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale
        );

        $this->maxLength = $maxLength;
        $this->isTextArea = $isTextArea;
        $this->validationRule = $validationRule;
        $this->regularExpression = $regularExpression;
        $this->isRichTextEditor = $isRichTextEditor;
    }

    public static function createText(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxLength $maxLength,
        AttributeValidationRule $validationRule,
        AttributeRegularExpression $regularExpression
    ) {
        return new self(
            $identifier,
            $enrichedEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale,
            $maxLength,
            AttributeIsTextArea::fromBoolean(false),
            $validationRule,
            $regularExpression,
            AttributeIsRichTextEditor::fromBoolean(false)
        );
    }

    public static function createTextArea(
        AttributeIdentifier $identifier,
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $code,
        LabelCollection $labelCollection,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeValuePerChannel $valuePerChannel,
        AttributeValuePerLocale $valuePerLocale,
        AttributeMaxLength $maxLength,
        AttributeIsRichTextEditor $isRichTextEditor
    ) {
        return new self(
            $identifier,
            $enrichedEntityIdentifier,
            $code,
            $labelCollection,
            $order,
            $isRequired,
            $valuePerChannel,
            $valuePerLocale,
            $maxLength,
            AttributeIsTextArea::fromBoolean(true),
            AttributeValidationRule::none(),
            AttributeRegularExpression::none(),
            $isRichTextEditor
        );
    }

    public function setMaxLength(AttributeMaxLength $newMaxLength): void
    {
        $this->maxLength = $newMaxLength;
    }

    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [
                'max_length'          => $this->maxLength->normalize(),
                'is_text_area'        => $this->isTextArea->normalize(),
                'is_rich_text_editor' => $this->isRichTextEditor->normalize(),
                'validation_rule'     => $this->validationRule->normalize(),
                'regular_expression'  => $this->regularExpression->normalize(),
            ]
        );
    }

    public function setIsTextArea(AttributeIsTextArea $isTextArea): void
    {
        if ($this->isTextArea->equals($isTextArea)) {
            return;
        }
        $this->isTextArea = $isTextArea;
        $this->isRichTextEditor = AttributeIsRichTextEditor::fromBoolean(false);
        $this->validationRule = AttributeValidationRule::none();
        $this->regularExpression = AttributeRegularExpression::none();
    }

    public function setValidationRule(AttributeValidationRule $validationRule): void
    {
        if ($this->isTextArea->isYes()) {
            throw new \LogicException('Cannot update the validation rule when the text area flag is true');
        }
        $this->validationRule = $validationRule;
        if (!$this->validationRule->isRegex()) {
            $this->regularExpression = AttributeRegularExpression::none();
        }
    }

    public function setRegularExpression(AttributeRegularExpression $regularExpression): void
    {
        if ($this->isTextArea->isYes()) {
            throw new \LogicException('Cannot update the regular expression when the text area flag is true');
        }
        if (!$this->validationRule->isRegex()) {
            throw new \LogicException('Cannot update the regular expression when the validation rule is not set to regular expression');
        }
        $this->regularExpression = $regularExpression;
    }

    public function setIsRichTextEditor(AttributeIsRichTextEditor $isRichTextEditor): void
    {
        if (!$this->isTextArea->isYes()) {
            throw new \LogicException('Cannot update the is rich text editor flag when the text area flag is false');
        }
        $this->isRichTextEditor = $isRichTextEditor;
    }

    public function isTextArea(): bool
    {
        return $this->isTextArea->isYes();
    }

    public function isValidationRuleRegularExpression(): bool
    {
        return $this->validationRule->isRegex();
    }

    protected function getType(): string
    {
        return self::ATTRIBUTE_TYPE;
    }
}
