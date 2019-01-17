<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Context;

use Akeneo\Test\Acceptance\Catalog\InMemoryGroupTypeRepository;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupTypeValidation implements Context
{
    /** @var InMemoryGroupTypeRepository */
    private $groupTypeRepository;

    /** @var EntityBuilder */
    private $groupTypeBuilder;

    /** @var ValidatorInterface */
    private $validator;

    /** @var GroupTypeInterface */
    private $groupType = null;

    /** @var string|null */
    private $fieldToValidate = null;

    public function __construct(
        InMemoryGroupTypeRepository $groupTypeRepository,
        EntityBuilder $groupTypeBuilder,
        ValidatorInterface $validator
    ) {
        $this->groupTypeRepository = $groupTypeRepository;
        $this->groupTypeBuilder = $groupTypeBuilder;
        $this->validator = $validator;
    }

    /**
     * @When I create an group type with a code :code
     */
    public function iCreateAGroupTypeWithCode(string $code)
    {
        $this->groupType = $this->groupTypeBuilder->build(['code' => $code], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @When I create a group type with a code > :count characters
     */
    public function iCreateAGroupTypeWithCodeSupToXCharacters(int $count)
    {
        $this->groupType = $this->groupTypeBuilder->build(['code' => str_repeat('a', $count+=1)], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @Then the group type should be invalid with message :message
     */
    public function theGroupTypeShouldBeInvalidWithMessageForField(string $message)
    {
        $violations = $this->validator->validate($this->groupType);

        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === $this->fieldToValidate) {
                Assert::assertSame($message, $violation->getMessage());

                return;
            }
        }

        throw new \Exception(sprintf('Cannot find error "%s" for the field "%s"', $message, $this->fieldToValidate));
    }
}
