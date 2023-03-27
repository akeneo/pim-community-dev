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
use Akeneo\Category\Infrastructure\Validation\LimitNumberOfAttributesInTheTemplate;
use Akeneo\Category\Infrastructure\Validation\LimitNumberOfAttributesInTheTemplateValidator;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LimitNumberOfAttributesInTheTemplateValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType(LimitNumberOfAttributesInTheTemplateValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    public function it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->duringValidate(1, new Type([]));
    }

    public function it_throws_an_exception_when_the_limit_of_attributes_in_the_template_is_reached(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
        GetAttribute $getAttribute,
    ): void
    {
        $templateUuid = TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330');

        $constraint = new LimitNumberOfAttributesInTheTemplate();
        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalledOnce()
            ->willReturn($violationBuilder);
        $violationBuilder->setCode('attributes_limit_reached')->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $attributeCollection = AttributeCollection::fromArray([]);
        for ($i = 0; $i < 50; $i++) {
            $attributeCollection->addAttribute(
                Attribute::fromType(
                    type: new AttributeType(AttributeType::TEXT),
                    uuid: AttributeUuid::fromUuid(Uuid::uuid4()),
                    code: new AttributeCode('attribute_code' . $i),
                    order: AttributeOrder::fromInteger($i),
                    isRequired: AttributeIsRequired::fromBoolean(false),
                    isScopable: AttributeIsScopable::fromBoolean(true),
                    isLocalizable: AttributeIsLocalizable::fromBoolean(true),
                    labelCollection: LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                    templateUuid: TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                    additionalProperties: AttributeAdditionalProperties::fromArray([]),
                )
            );
        }
        $getAttribute->byTemplateUuid($templateUuid)->shouldBeCalled()->willReturn($attributeCollection);

        $command = AddAttributeCommand::create(
            code: 'attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: $templateUuid->getValue(),
            locale: 'en_US',
            label: 'The attribute',
        );
        $this->validate($command, $constraint);
    }
}
