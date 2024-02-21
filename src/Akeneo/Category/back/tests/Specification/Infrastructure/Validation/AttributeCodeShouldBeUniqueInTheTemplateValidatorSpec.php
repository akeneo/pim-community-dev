<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Validation\AttributeCodeShouldBeUniqueInTheTemplate;
use Akeneo\Category\Infrastructure\Validation\AttributeCodeShouldBeUniqueInTheTemplateValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCodeShouldBeUniqueInTheTemplateValidatorSpec extends ObjectBehavior
{
    public function let(
        ExecutionContext $context,
        GetAttribute $getAttribute,
    ): void {
        $this->beConstructedWith($getAttribute);

        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AttributeCodeShouldBeUniqueInTheTemplateValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(1, new Type([]));
    }

    public function it_does_nothing_when_there_is_no_attribute_in_the_template(
        ExecutionContext $context,
        GetAttribute $getAttribute,
    ): void {
        /** @var TemplateUuid $templateUuid */
        $templateUuid = $this->getData()['templateUuid'];

        $context->getObject()->willReturn(
            AddAttributeCommand::create(
                code: 'other_attribute_code',
                type: 'text',
                isScopable: true,
                isLocalizable: true,
                templateUuid: $templateUuid->getValue(),
                locale: 'en_US',
                label: 'The attribute',
            )
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $getAttribute->byTemplateUuid($templateUuid)->shouldBeCalled()->willReturn(
            AttributeCollection::fromArray([])
        );

        $this->validate('other_attribute_code', new AttributeCodeShouldBeUniqueInTheTemplate());
    }

    public function it_does_nothing_when_the_attribute_code_is_unique_in_the_template(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        GetAttribute $getAttribute,
    ): void
    {
        /** @var TemplateUuid $templateUuid */
        $templateUuid = $this->getData()['templateUuid'];

        $context->getObject()->willReturn(
            AddAttributeCommand::create(
                code: 'other_attribute_code',
                type: 'text',
                isScopable: true,
                isLocalizable: true,
                templateUuid: $templateUuid->getValue(),
                locale: 'en_US',
                label: 'The attribute',
            )
        );

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $getAttribute->byTemplateUuid($templateUuid)->shouldBeCalled()->willReturn(
            AttributeCollection::fromArray([$this->getData()['attribute']])
        );

        $this->validate('other_attribute_code', new AttributeCodeShouldBeUniqueInTheTemplate());
    }

    public function it_throws_an_exception_when_the_attribute_code_is_not_unique_in_the_template(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        GetAttribute $getAttribute,
    ): void {
        /** @var TemplateUuid $templateUuid */
        $templateUuid = $this->getData()['templateUuid'];

        $context->getObject()->willReturn(
            AddAttributeCommand::create(
                code: 'same_attribute_code',
                type: 'text',
                isScopable: true,
                isLocalizable: true,
                templateUuid: $templateUuid->getValue(),
                locale: 'en_US',
                label: 'The attribute',
            )
        );

        $constraint = new AttributeCodeShouldBeUniqueInTheTemplate();
        $context
            ->buildViolation($constraint->message, ['{{ attributeCode }}' => 'same_attribute_code'])
            ->shouldBeCalledOnce()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $getAttribute->byTemplateUuid($templateUuid)->shouldBeCalled()->willReturn(
            AttributeCollection::fromArray([$this->getData()['attribute']])
        );

        $this->validate('same_attribute_code', $constraint);
    }

    private function getData(): array
    {
        return [
            'templateUuid' => TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            'attribute' => Attribute::fromType(
                type: new AttributeType(AttributeType::TEXT),
                uuid: AttributeUuid::fromString('b777dfe6-2518-4d0e-958d-ddb07c81b7b6'),
                code: new AttributeCode('same_attribute_code'),
                order: AttributeOrder::fromInteger(1),
                isRequired: AttributeIsRequired::fromBoolean(false),
                isScopable: AttributeIsScopable::fromBoolean(true),
                isLocalizable: AttributeIsLocalizable::fromBoolean(true),
                labelCollection: LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                templateUuid: TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                additionalProperties: AttributeAdditionalProperties::fromArray([]),
            )
        ];
    }
}
