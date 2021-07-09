<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\AssetCodeShouldBeUnique;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\AssetCodeShouldBeUniqueValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AssetCodeShouldBeUniqueValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context,AssetExistsInterface $assetExists)
    {
        $this->beConstructedWith($assetExists);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCodeShouldBeUniqueValidator::class);
    }

    function it_adds_violation_when_asset_exists_in_database(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $constraintViolationBuilder, AssetExistsInterface $assetExists) {
        $constraint = new AssetCodeShouldBeUnique();
        $code = "a";
        $command = new CreateAssetCommand("",$code,[]);
        $assetExists->withCode($code)->willReturn(true);
        $this->initConstraintViolationBuilder($context, $constraintViolationBuilder, $code);
        $this->validate($command,$constraint);
    }

    function it_adds_violation_when_asset_exists_in_memory(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $constraintViolationBuilder, AssetExistsInterface $assetExists) {
        $constraint = new AssetCodeShouldBeUnique();
        $code1 = "a";
        $code2= "A";
        $command1 = new CreateAssetCommand("",$code1,[]);
        $command2 = new CreateAssetCommand("",$code2,[]);
        $assetExists->withCode($code1)->willReturn(false);
        $assetExists->withCode($code2)->willReturn(false);
        $this->initConstraintViolationBuilder($context, $constraintViolationBuilder, $code2);
        $this->validate($command1,$constraint);
        $this->validate($command2,$constraint);
    }

    function it_validates_when_no_asset_exists(ExecutionContextInterface $context, AssetExistsInterface $assetExists) {
        $constraint = new AssetCodeShouldBeUnique();
        $code = "a";
        $command = new CreateAssetCommand("",$code,[]);
        $assetExists->withCode($code)->willReturn(false);
        $context->buildViolation(AssetCodeShouldBeUnique::ERROR_MESSAGE)->shouldNotBeCalled();
        $this->validate($command,$constraint);
    }

    protected function initConstraintViolationBuilder($context, $constraintViolationBuilder, string $code): void
    {
        $context->buildViolation(AssetCodeShouldBeUnique::ERROR_MESSAGE)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->setParameter('%code%', $code)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('code')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();
    }

}
