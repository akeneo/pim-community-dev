<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute\Context;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttributeValidation implements Context
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var InMemoryAttributeGroupRepository */
    private $attributeGroupRepository;

    /** @var EntityBuilder */
    private $attributeGroupBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var AttributeInterface|null */
    private $attribute = null;

    /** @var string|null */
    private $fieldToValidate = null;

    public function __construct(
        InMemoryAttributeRepository $attributeRepository,
        EntityBuilder $attributeBuilder,
        InMemoryAttributeGroupRepository $attributeGroupRepository,
        EntityBuilder $attributeGroupBuilder,
        SaverInterface $attributeSaver,
        ValidatorInterface $validator
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeBuilder = $attributeBuilder;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupBuilder = $attributeGroupBuilder;
        $this->attributeSaver = $attributeSaver;
        $this->validator = $validator;
    }

    /**
     * @When I create an attribute with a code :code
     */
    public function iCreateAnAttributeWithACode(string $code)
    {
        $this->attribute = $this->attributeBuilder->build(['code' => $code], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @When I create an attribute with a code with a suffix :suffix
     */
    public function iCreateAnAttributeWithCodeWithSuffix(string $suffix)
    {
        $this->attribute = $this->attributeBuilder->build(['code' => 'random_' . $suffix], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @When I create an attribute with an invalid regex
     */
    public function iCreateAnAttributeWithAnInvalidRegex()
    {
        $this->buildAttributeGroup();

        $this->attribute = $this->attributeBuilder->build([
            'code' => 'random_code',
            'validation_rule' => 'Regular expression',
            'validation_regexp' => 'not a valid regexp',
            'type' => AttributeTypes::TEXT,
            'group' => 'MANDATORY_ATTRIBUTE_GROUP_CODE'
        ], false);
        $this->fieldToValidate = 'validationRegexp';
    }

    /**
     * @When I create an attribute with an empty :field
     */
    public function iCreateAnAttributeWithAnEmptyField(string $field)
    {
        $this->attribute = $this->attributeBuilder->build([
            'code' => 'random_code',
        ], false);
        $this->fieldToValidate = $field;
    }

    /**
     * @When I create an attribute with a code > :count characters
     */
    public function iCreateAnAttributeWithACodeSupToXCharacters(int $count)
    {
        $this->buildAttributeGroup();

        $this->attribute = $this->attributeBuilder->build([
            'code' => str_repeat('a', $count+=1),
            'type' => AttributeTypes::TEXT,
            'group' => 'MANDATORY_ATTRIBUTE_GROUP_CODE',
        ], false);
        $this->fieldToValidate = 'code';
    }

    /**
     * @When I create a second identifier attribute
     */
    public function iCreateASecondIdentifierAttribute()
    {
        $this->buildAttributeGroup();

        $attribute = $this->attributeBuilder->build([
            'code' => 'identifier_1',
            'type' => AttributeTypes::IDENTIFIER,
            'group' => 'MANDATORY_ATTRIBUTE_GROUP_CODE',
            'useable_as_grid_filter' => true
        ]);

        $this->attributeSaver->save($attribute);

        $this->attribute = $this->attributeBuilder->build([
            'code' => 'identifier_2',
            'type' => AttributeTypes::IDENTIFIER,
            'group' => 'MANDATORY_ATTRIBUTE_GROUP_CODE',
            'useable_as_grid_filter' => true,
        ], false);

        $this->fieldToValidate = 'code';
    }

    /**
     * @Then the attribute should be invalid with message :message
     */
    public function theAttributeShouldBeInvalidWithMessageForField(string $message)
    {
        $violations = $this->validator->validate($this->attribute);

        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === $this->fieldToValidate) {
                Assert::assertSame($message, $violation->getMessage());

                return;
            }
        }

        throw new \Exception(sprintf('Cannot find error "%s" for the field "%s"', $message, $this->fieldToValidate));
    }

    private function buildAttributeGroup(string $code = 'MANDATORY_ATTRIBUTE_GROUP_CODE'): void
    {
        $attributeGroup = $this->attributeGroupBuilder->build(['code' => $code]);
        $this->attributeGroupRepository->save($attributeGroup);
    }
}
