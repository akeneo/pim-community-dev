<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldHaveAnActivatedTemplate;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldHaveAnActivatedTemplateValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueUserIntentsShouldHaveAnActivatedTemplateValidatorSpec extends ObjectBehavior
{
    public function let(
        ExecutionContext $context,
        GetAttribute $getAttribute,
        IsTemplateDeactivated $isTemplateDeactivated
    ): void {
        $this->beConstructedWith($getAttribute, $isTemplateDeactivated);

        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ValueUserIntentsShouldHaveAnActivatedTemplateValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(1, new Type([]));
    }

    public function it_does_nothing_when_the_attribute_value_is_linked_to_an_activated_template(
        ExecutionContext $context,
        GetAttribute $getAttribute,
        IsTemplateDeactivated $isTemplateDeactivated
    ): void {
        /** @var AttributeTextArea $textAreaSEOMetaDescriptionAttribute */
        $textAreaSEOMetaDescriptionAttribute = $this->getAttributes()[0];
        /** @var AttributeTextArea $textAreaSEOKeyWordsAttribute */
        $textAreaSEOKeyWordsAttribute = $this->getAttributes()[1];

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $getAttribute->byUuids([$textAreaSEOMetaDescriptionAttribute->getUuid()])->willReturn(
            AttributeCollection::fromArray([$textAreaSEOMetaDescriptionAttribute])
        );

        $isTemplateDeactivated->__invoke($textAreaSEOKeyWordsAttribute->getTemplateUuid())->willReturn(false)->shouldBeCalled();

        $this->validate([
            new SetTextArea(
                (string) $textAreaSEOMetaDescriptionAttribute->getUuid(),
                (string) $textAreaSEOMetaDescriptionAttribute->getCode(),
                'ecommerce',
                'en_US',
                'SEO meta description'
            ),
            new SetTextArea(
                (string) $textAreaSEOKeyWordsAttribute->getUuid(),
                (string) $textAreaSEOKeyWordsAttribute->getCode(),
                'ecommerce',
                'en_US',
                'SEO keywords'
            ),
        ], new ValueUserIntentsShouldHaveAnActivatedTemplate());
    }

    public function it_throws_an_exception_when_the_attribute_value_is_linked_to_a_deactivated_template(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        GetAttribute $getAttribute,
        IsTemplateDeactivated $isTemplateDeactivated
    ): void {
        /** @var AttributeTextArea $textAreaSEOMetaDescriptionAttribute */
        $textAreaSEOMetaDescriptionAttribute = $this->getAttributes()[0];
        /** @var AttributeTextArea $textAreaSEOKeyWordsAttribute */
        $textAreaSEOKeyWordsAttribute = $this->getAttributes()[1];

        $constraint = new ValueUserIntentsShouldHaveAnActivatedTemplate();
        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalledOnce()
            ->willReturn($violationBuilder);
        $violationBuilder->setCode('deactivated_template')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $getAttribute->byUuids([$textAreaSEOMetaDescriptionAttribute->getUuid()])->willReturn(
            AttributeCollection::fromArray([$textAreaSEOMetaDescriptionAttribute])
        );

        $isTemplateDeactivated->__invoke($textAreaSEOKeyWordsAttribute->getTemplateUuid())->willReturn(true)->shouldBeCalled();

        $this->validate([
            new SetTextArea(
                (string) $textAreaSEOMetaDescriptionAttribute->getUuid(),
                (string) $textAreaSEOMetaDescriptionAttribute->getCode(),
                'ecommerce',
                'en_US',
                'SEO meta description'
            ),
            new SetTextArea(
                (string) $textAreaSEOKeyWordsAttribute->getUuid(),
                (string) $textAreaSEOKeyWordsAttribute->getCode(),
                'ecommerce',
                'en_US',
                'SEO keywords'
            ),
        ], new ValueUserIntentsShouldHaveAnActivatedTemplate());
    }

    private function getAttributes(): array
    {
        return [
            AttributeTextArea::create(
                AttributeUuid::fromString('b777dfe6-2518-4d0e-958d-ddb07c81b7b6'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(11),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeTextArea::create(
                AttributeUuid::fromString('1efc3af6-e89c-4281-9bd5-b827d9397cf7'),
                new AttributeCode('seo_keywords'),
                AttributeOrder::fromInteger(13),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO keywords']),
                TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                AttributeAdditionalProperties::fromArray([]),
            )
        ];
    }
}
