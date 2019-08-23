<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\RuleEngineValidatorACLInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetAction;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleEngineValidatorACLSpec extends ObjectBehavior
{
    function let(
        ValidatorInterface $productConditionValidator,
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productActionValidator
    ) {
        $this->beConstructedWith($actionDenormalizer, $productConditionValidator, $productActionValidator);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(RuleEngineValidatorACLInterface::class);
    }

    function it_validates_a_product_condition_and_returns_violations(ValidatorInterface $productConditionValidator)
    {
        $productSelection = [
            'field'    => 'family',
            'operator' => 'IN',
            'value'    => ['camcorders'],
            'locale'   => 'fr_FR',
            'channel'  => 'ecommerce',
        ];
        $oneViolation = $this->oneViolation();

        $productConditionValidator->validate(
            Argument::that(function (ProductCondition $actualCondition) use ($productSelection) {
                return $productSelection['field'] === $actualCondition->getField()
                    && $productSelection['operator'] === $actualCondition->getOperator()
                    && $productSelection['value'] === $actualCondition->getValue()
                    && $productSelection['locale'] === $actualCondition->getLocale()
                    && $productSelection['channel'] === $actualCondition->getScope();
            })
        )->willReturn($oneViolation);

        $this->validateProductSelection($productSelection)->shouldReturn($oneViolation);
    }

    function it_validates_a_product_set_action_and_returns_violations(
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productActionValidator
    ) {
        $productAction = [
            'mode'    => 'replace',
            'attribute' => 'attribute',
        ];
        $setProductAction = new ProductSetAction(['field' => 'attribute']);
        $oneViolation = $this->oneViolation();

        $actionDenormalizer->denormalize(
            Argument::that(function (array $adaptedProductAction) use ($productAction) {
                return 'set' === $adaptedProductAction['type']
                    && $adaptedProductAction['field'] === $productAction['attribute']
                    && ['VALIDATION_TEST'] === $adaptedProductAction['items'];
            }
            ),
            ActionInterface::class
        )->willReturn($setProductAction);

        $productActionValidator->validate(
            Argument::that(function (ProductSetAction $actualAction) use ($productAction) {
                return $productAction['attribute'] === $actualAction->getField();
            })
        )->willReturn($oneViolation);

        $this->validateProductAssignment($productAction)->shouldReturn($oneViolation);
    }

    function it_validates_a_product_add_action_and_returns_violations(
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productActionValidator
    ) {
        $productAction = [
            'mode'    => 'add',
            'attribute' => 'attribute',
        ];
        $setProductAction = new ProductAddAction(['field' => 'attribute', 'items' => ['one', 'two']]);
        $oneViolation = $this->oneViolation();

        $actionDenormalizer->denormalize(
            Argument::that(function (array $adaptedProductAction) use ($productAction) {
                return $productAction['mode'] === $adaptedProductAction['type']
                    && $adaptedProductAction['field'] === $productAction['attribute']
                    && ['VALIDATION_TEST'] === $adaptedProductAction['items'];
            }
            ),
            ActionInterface::class
        )->willReturn($setProductAction);

        $productActionValidator->validate(
            Argument::that(function (ProductAddAction $actualAction) use ($productAction) {
                return $productAction['attribute'] === $actualAction->getField();
            })
        )->willReturn($oneViolation);

        $this->validateProductAssignment($productAction)->shouldReturn($oneViolation);
    }

    private function oneViolation(): ConstraintViolationList
    {
        return new ConstraintViolationList([
            new ConstraintViolation('', '', [], '', '', '')
        ]);
    }
}
