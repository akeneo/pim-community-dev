<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Family\Context;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FamilyValidation implements Context
{
    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var EntityBuilder */
    private $familyBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var FamilyInterface */
    private $family = null;

    /** @var string|null */
    private $fieldToValidate = null;

    public function __construct(
        InMemoryFamilyRepository $familyRepository,
        EntityBuilder $familyBuilder,
        SaverInterface $attributeSaver,
        ValidatorInterface $validator
    ) {
        $this->familyRepository = $familyRepository;
        $this->familyBuilder = $familyBuilder;
        $this->attributeSaver = $attributeSaver;
        $this->validator = $validator;
    }

    /**
     * @When I create an family with a code :code
     */
    public function iCreateAnFamilyWithCode(string $code)
    {
        $this->generateIdentifierAttribute();

        $this->family = $this->familyBuilder->build(['code' => $code], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @When I create a family with a code > :count characters
     */
    public function iCreateAFamilyWithCodeSupToXCharacters(int $count)
    {
        $this->generateIdentifierAttribute();

        $this->family = $this->familyBuilder->build(['code' => str_repeat('a', $count+=1)], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @Then the family should be invalid with message :message
     */
    public function theFamilyShouldBeInvalidWithMessageForField(string $message)
    {
        $violations = $this->validator->validate($this->family);

        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === $this->fieldToValidate) {
                Assert::assertSame($message, $violation->getMessage());

                return;
            }
        }

        throw new \Exception(sprintf('Cannot find error "%s" for the field "%s"', $message, $this->fieldToValidate));
    }

    private function generateIdentifierAttribute(): void
    {
        $attribute = (new Builder())->aIdentifier()->withCode('identifier')->build();

        $this->attributeSaver->save($attribute);
    }
}
