<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AssociationType\Context;

use Akeneo\Test\Acceptance\AssociationType\InMemoryAssociationTypeRepository;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AssociationTypeValidation implements Context
{
    /** @var InMemoryAssociationTypeRepository */
    private $associationTypeRepository;

    /** @var EntityBuilder */
    private $associationTypeBuilder;

    /** @var ValidatorInterface */
    private $validator;

    /** @var AssociationTypeInterface|null */
    private $associationType = null;

    /** @var string|null */
    private $fieldToValidate = null;

    public function __construct(
        InMemoryAssociationTypeRepository $associationTypeRepository,
        EntityBuilder $associationTypeBuilder,
        ValidatorInterface $validator
    ) {
        $this->associationTypeRepository = $associationTypeRepository;
        $this->associationTypeBuilder = $associationTypeBuilder;
        $this->validator = $validator;
    }

    /**
     * @When I create an association type with a code :code
     */
    public function iCreateAnAssociationTypeWithCode(string $code)
    {
        $this->associationType = $this->associationTypeBuilder->build(['code' => $code], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @When I create an association type with a code > :count characters
     */
    public function iCreateAnAttributeWithACodeSupToXCharacters(int $count)
    {
        $this->associationType = $this->associationTypeBuilder->build([
            'code' => str_repeat('a', $count+=1),
        ], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @Then the association type should be invalid with message :message
     */
    public function theAssociationTypeShouldBeInvalidWithMessageForField(string $message)
    {
        $violations = $this->validator->validate($this->associationType);

        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === $this->fieldToValidate) {
                Assert::assertSame($message, $violation->getMessage());

                return;
            }
        }

        throw new \Exception(sprintf('Cannot find error "%s" for the field "%s"', $message, $this->fieldToValidate));
    }
}
