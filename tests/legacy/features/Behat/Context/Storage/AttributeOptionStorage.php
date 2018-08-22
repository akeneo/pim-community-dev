<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Storage;

use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class AttributeOptionStorage implements Context
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeOptionRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeOptionRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(IdentifiableObjectRepositoryInterface $attributeOptionRepository, EntityManagerInterface $entityManager)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param TableNode $table Must contains columns "attribute" and "code".
     *
     * @throws \Exception
     *
     * @Then /^there should be the following attribute options:$/
     */
    public function thereShouldBeTheFollowingAttributeOptions($table): void
    {
        $this->entityManager->clear();

        foreach ($table->getHash() as $attributeOptionData) {
            $attributeOption = $this->getAttribute($attributeOptionData);
            unset($attributeOptionData['attribute']);
            unset($attributeOptionData['code']);

            foreach ($attributeOptionData as $property => $expectedValue) {
                if (preg_match('/^label-(?<locale>.*)$/', $property, $matches)) {
                    $this->assertAttributeOptionLabelEqual($expectedValue, $matches['locale'], $attributeOption);
                } else {
                    // Implement other properties if needed.
                    throw new \Exception(sprintf('The property "%s" is not supported', $property));
                }
            }
        }
    }

    /**
     * @param array $attributeOptionData
     *
     * @throws \Exception If the attribute option does not exist
     *
     * @return AttributeOption
     */
    private function getAttribute(array $attributeOptionData): AttributeOption
    {
        if (!isset($attributeOptionData['attribute'])) {
            throw new \Exception('You must give the attribute code in the column "attribute"');
        }

        if (!isset($attributeOptionData['code'])) {
            throw new \Exception('You must give the option code in the column "code"');
        }

        $attributeOptionIdentifier = sprintf('%s.%s', $attributeOptionData['attribute'], $attributeOptionData['code']);
        $attributeOption = $this->attributeOptionRepository->findOneByIdentifier($attributeOptionIdentifier);

        if (null === $attributeOption) {
            throw new \Exception(sprintf(
                'There is no option "%s" for the attribute "%s"',
                $attributeOptionData['code'],
                $attributeOptionData['attribute']
            ));
        }

        return $attributeOption;
    }

    /**
     * @param string $expectedLabel
     * @param string $locale
     * @param AttributeOptionInterface $attributeOption
     *
     * @throws \Exception
     */
    private function assertAttributeOptionLabelEqual(string $expectedLabel, string $locale, AttributeOptionInterface $attributeOption): void
    {
        $attributeOption->setLocale($locale);
        $optionValue = $attributeOption->getOptionValue();

        Assert::assertNotNull($optionValue, sprintf(
            'The option "%s" of the attribute "%s" has no label for the locale "%s"',
            $attributeOption->getCode(),
            $attributeOption->getAttribute()->getCode(),
            $locale
        ));

        $label = $optionValue->getLabel();

        Assert::assertEquals($expectedLabel, $label, sprintf(
            'The label "%s" of the option "%s" of the attribute "%s" is not equal to the expected one',
            $locale,
            $attributeOption->getCode(),
            $attributeOption->getAttribute()->getCode()
        ));
    }
}
