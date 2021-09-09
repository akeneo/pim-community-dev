<?php

declare(strict_types=1);

namespace AkeneoTest\Acceptance\Attribute;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValidationContext implements Context
{
    /** @var SimpleFactoryInterface */
    private $attributeFactory;

    /** @var ObjectUpdaterInterface */
    private $attributeUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationList */
    private $violations;

    public function __construct(
        SimpleFactoryInterface $attributeFactory,
        ObjectUpdaterInterface $attributeUpdater,
        ValidatorInterface $validator
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->validator = $validator;
    }

    /**
     * @When an attribute is created with the code :code
     */
    public function anAttributeIsCreatedWithTheCode(string $code)
    {
        $attribute = $this->attributeFactory->create();
        $this->attributeUpdater->update($attribute, [
            'code' => $code,
            'type' => 'pim_catalog_text',
        ]);

        $this->violations = $this->validator->validate($attribute);
    }

    /**
     * @Then I should see a validation error :message
     */
    public function iShouldSeeAValidationError(string $message)
    {
        Assert::isInstanceOf($this->violations, ConstraintViolationList::class);
        Assert::true($this->hasViolationWithMessage($message));
    }

    private function hasViolationWithMessage(string $message): bool
    {
        foreach ($this->violations as $violation) {
            if ($message === $violation->getMessage()) {
                return true;
            }
        }

        return false;
    }
}
