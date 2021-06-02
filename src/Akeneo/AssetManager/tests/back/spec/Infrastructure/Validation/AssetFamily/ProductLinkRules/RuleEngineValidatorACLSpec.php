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
            Argument::that(fn(ProductCondition $actualCondition) => $productSelection['field'] === $actualCondition->getField()
                && $productSelection['operator'] === $actualCondition->getOperator()
                && $productSelection['value'] === $actualCondition->getValue()
                && $productSelection['locale'] === $actualCondition->getLocale()
                && $productSelection['channel'] === $actualCondition->getScope())
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
            Argument::that(fn(array $adaptedProductAction) => 'set' === $adaptedProductAction['type']
                && $adaptedProductAction['field'] === $productAction['attribute']
                && ['VALIDATION_TEST'] === $adaptedProductAction['items']
            ),
            ActionInterface::class
        )->willReturn($setProductAction);

        $productActionValidator->validate(
            Argument::that(fn(ProductSetAction $actualAction) => $productAction['attribute'] === $actualAction->getField())
        )->willReturn($oneViolation);

        $actualViolations = $this->validateProductAssignment($productAction);
        $actualViolations->count()->shouldBe(1);
    }

    function it_validates_a_product_add_action_and_returns_violations(
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productActionValidator
    ) {
        $productAction = [
            'mode'    => 'add',
            'attribute' => 'attribute',
        ];
        $productAddAction = new ProductAddAction(['field' => 'attribute', 'items' => ['one', 'two']]);
        $oneViolation = $this->oneViolation();

        $actionDenormalizer->denormalize(
            Argument::that(fn(array $adaptedProductAction) => $productAction['mode'] === $adaptedProductAction['type']
                && $adaptedProductAction['field'] === $productAction['attribute']
                && ['VALIDATION_TEST'] === $adaptedProductAction['items']
            ),
            ActionInterface::class
        )->willReturn($productAddAction);

        $productActionValidator->validate(
            Argument::that(fn(ProductAddAction $actualAction) => $productAction['attribute'] === $actualAction->getField())
        )->willReturn($oneViolation);

        $actualViolations = $this->validateProductAssignment($productAction);
        $actualViolations->count()->shouldBe(1);
    }

    function it_does_not_return_violations_due_to_the_dummy_asset_code_not_existing_for_replace_mode(
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productActionValidator
    ) {
        $setProductAction = new ProductAddAction(['field' => 'attribute', 'items' => ['one', 'two']]);
        $assetDoesNotExistViolation = $this->assetDoesNotExistViolationWithReplaceMode();
        $actionDenormalizer->denormalize(Argument::any(), ActionInterface::class)->willReturn($setProductAction);
        $productActionValidator->validate(Argument::any())->willReturn($assetDoesNotExistViolation);

        $actualViolations = $this->validateProductAssignment(['mode' => 'add', 'attribute' => 'attribute']);

        $actualViolations->count()->shouldBe(0);
    }

    function it_does_not_return_violations_due_to_the_dummy_asset_code_not_existing_for_add_mode(
        DenormalizerInterface $actionDenormalizer,
        ValidatorInterface $productActionValidator
    ) {
        $productAddAction = new ProductAddAction(['field' => 'attribute', 'items' => ['one', 'two']]);
        $assetDoesNotExistViolation = $this->assetDoesNotExistViolationWithAddMode();
        $actionDenormalizer->denormalize(Argument::any(), ActionInterface::class)->willReturn($productAddAction);
        $productActionValidator->validate(Argument::any())->willReturn($assetDoesNotExistViolation);

        $actualViolations = $this->validateProductAssignment(['mode' => 'add', 'attribute' => 'attribute']);

        $actualViolations->count()->shouldBe(0);
    }

    private function oneViolation(): ConstraintViolationList
    {
        return new ConstraintViolationList([
            new ConstraintViolation('', '', [], '', '', '')
        ]);
    }

    private function assetDoesNotExistViolationWithReplaceMode(): ConstraintViolationList
    {
        $root = new ProductSetAction(['value' => ['VALIDATION_TEST']]);
        return new ConstraintViolationList([
            new ConstraintViolation('', '', [], $root, '', '')
        ]);
    }

    private function assetDoesNotExistViolationWithAddMode(): ConstraintViolationList
    {
        $root = new ProductAddAction(['items' => ['VALIDATION_TEST']]);
        return new ConstraintViolationList([
            new ConstraintViolation('', '', [], $root, '', '')
        ]);
    }
}
