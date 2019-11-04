<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ScopableValues;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ScopableValuesValidator;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableValuesValidatorSpec extends ObjectBehavior
{
    function let(ChannelRepositoryInterface $channelRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($channelRepository);
        $this->initialize($context);

        $channel = new Channel();
        $channel->setCode('ecommerce');
        $channelRepository->findAll()->willReturn([$channel]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ScopableValuesValidator::class);
    }

    function it_is_a_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_validates_the_value_collection_with_valid_scope(ExecutionContextInterface $context)
    {
        $collection = new WriteValueCollection([
            ScalarValue::value('sku', 'my_identifier'),
            ScalarValue::scopableValue('description', 'An awesome description', 'ecommerce'),
            DateValue::value('release_date', new \DateTime()),
        ]);

        $context->buildViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate($collection, new ScopableValues());
    }

    function it_adds_a_violation_with_unknown_scope(
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ScopableValues();
        $collection = new WriteValueCollection([
            ScalarValue::value('sku', 'my_identifier'),
            ScalarValue::scopableValue('description', 'An awesome description', 'unknown'),
            DateValue::value('release_date', new \DateTime()),
        ]);

        $context->buildViolation($constraint->unknownScopeMessage, [
            '%attribute_code%' => 'description',
            '%channel%' => 'unknown'
        ])->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($collection, $constraint);
    }
}
