<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Behat\Behat\Context\Context;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateIdentifierGeneratorContext implements Context
{
    public const DEFAULT_CODE = 'generator_0';

    public function __construct(
        private readonly ViolationsContext $violationsContext,
        private readonly CreateGeneratorHandler $createGeneratorHandler,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly AttributeOptionRepositoryInterface $attributeOptionRepository,
        private readonly FindFamilyCodes $findFamilyCodes,
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
    }

    /**
     * @Given /^the '(?P<attributeCode>[^']*)'(?P<localizable> localizable)?(?: and)?(?P<scopable> scopable)? attribute of type '(?P<attributeType>[^']*)'$/
     */
    public function theAttribute(
        string $attributeCode,
        string $attributeType,
        string $scopable = '',
        string $localizable = ''
    ): void {
        $identifierAttribute = new Attribute();
        $identifierAttribute->setType($attributeType);
        $identifierAttribute->setCode($attributeCode);
        $identifierAttribute->setScopable($scopable !== '');
        $identifierAttribute->setLocalizable($localizable !== '');
        $identifierAttribute->setBackendType(AttributeTypes::BACKEND_TYPE_TEXT);
        $this->attributeRepository->save($identifierAttribute);
    }

    /**
     * @Given the :familyCode family
     */
    public function theFamily(string $familyCode): void
    {
        $this->findFamilyCodes->save($familyCode);
    }

    /**
     * @Given /^the (?P<optionCodes>(('.*')(, | and )?)+) options? for '(?P<attributeCode>[^']*)' attribute$/
     */
    public function theAndOptionsForAttribute(string $optionCodes, string $attributeCode): void
    {
        foreach ($this->splitList($optionCodes) as $optionCode) {
            $attributeOption = new AttributeOption();
            $attributeOption->setCode($optionCode);
            $attributeOption->setAttribute($this->attributeRepository->findOneByIdentifier($attributeCode));
            $this->attributeOptionRepository->save($attributeOption);
        }
    }

    /**
     * @Given /^the '(?P<channelCode>[^']*)' channel having (?P<localeCodes>(('.*')(, | and )?)+) as locales?$/
     */
    public function theChannelHavingActiveLocalesAnd(string $channelCode, string $localeCodes): void
    {
        $channel = new Channel();
        $channel->setCode($channelCode);
        $locales = [];
        foreach ($this->splitList($localeCodes) as $localeCode) {
            $locale = new Locale();
            $locale->setCode($localeCode);
            $locale->addChannel($channel);
            $locales[] = $locale;
        }
        $channel->setLocales($locales);

        $this->channelRepository->save($channel);
    }

    /**
     * @When /^I create (?P<count>\d+|an) identifier generators?$/
     */
    public function iCreateAnIdentifierGenerator(string $count): void
    {
        $intCount = $count === 'an' ? 1 : \intval($count);
        for ($i = 0; $i < $intCount; $i++) {
            $this->tryToCreateGenerator(code: \sprintf('generator_%d', $i));
        }
    }

    /**
     * @When /^I try to create an identifier generator with target '(?P<target>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithTarget(string $target): void
    {
        $this->tryToCreateGenerator(target: $target);
    }

    /**
     * @When I try to create an identifier generator with blank structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithBlankStructure(): void
    {
        $this->tryToCreateGenerator(structure: []);
    }

    /**
     * @When I try to create an identifier generator with an unknown property
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnUnknownProperty(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'unknown', 'string' => 'a_string']]);
    }

    /**
     * @When I try to create an identifier generator with :propertiesCount properties in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithTooManyPropertiesInStructure(int $propertiesCount): void
    {
        $this->tryToCreateGenerator(
            structure:
            \array_fill(0, $propertiesCount, ['type' => 'free_text', 'string' => 'abcdef1'])
        );
    }

    /**
     * @When I create an identifier generator without label
     */
    public function iCreateAnIdentifierGeneratorWithoutLabel(): void
    {
        $this->tryToCreateGenerator(labels: []);
    }

    /**
     * @When /^I try to create an identifier generator with '(?P<locale>[^']*)' label '(?P<label>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithLabel(string $locale, string $label): void
    {
        $this->tryToCreateGenerator(labels: [$locale => $label]);
    }

    /**
     * @When /^I try to create an identifier generator with delimiter '(?P<delimiter>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithDelimiter(string $delimiter): void
    {
        $this->tryToCreateGenerator(delimiter: $delimiter);
    }

    /**
     * @When I create an identifier generator with delimiter null
     */
    public function iCreateAnIdentifierGeneratorWithDelimiterNull(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                self::DEFAULT_CODE,
                [],
                [['type' => 'free_text', 'string' => self::DEFAULT_CODE]],
                [],
                'sku',
                null,
                'no',
            ));
        } catch (ViolationsException $exception) {
            $this->violationsContext->setViolationsException($exception);
        }
    }

    /**
     * @When I try to create an identifier generator with text transformation :textTransformation
     */
    public function iTryToCreateAnIdentifierGeneratorWithTextTransformation(string $textTransformation): void
    {
        $this->tryToCreateGenerator(textTransformation: $textTransformation);
    }

    /**
     * @When /^I try to create an identifier generator with code '(?P<code>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithCode(string $code): void
    {
        $this->tryToCreateGenerator(code: $code);
    }

    /**
     * @When /^I try to create an identifier generator with unknown condition type$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithUnknownConditionType(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'unknown', 'value' => true],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with (\d+) conditions$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithConditions(string $count): void
    {
        $this->tryToCreateGenerator(conditions: \array_fill(0, \intval($count), $this->getValidCondition('simple_select')));
    }

    private function tryToCreateGenerator(
        ?string $code = null,
        ?array $structure = null,
        ?array $conditions = null,
        ?array $labels = null,
        ?string $target = null,
        ?string $delimiter = null,
        ?string $textTransformation = null,
    ): void {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                $code ?? self::DEFAULT_CODE,
                $conditions ?? [
                    $this->getValidCondition('enabled'),
                    $this->getValidCondition('family'),
                    $this->getValidCondition('simple_select'),
                    $this->getValidCondition('multi_select'),
                ],
                $structure ?? [['type' => 'free_text', 'string' => self::DEFAULT_CODE]],
                $labels ?? ['fr_FR' => 'Générateur'],
                $target ?? 'sku',
                $delimiter ?? '-',
                $textTransformation ?? 'no',
            ));
        } catch (ViolationsException $exception) {
            $this->violationsContext->setViolationsException($exception);
        }
    }

    private function getValidCondition(string $type, ?string $operator = null): array
    {
        switch($type) {
            case 'enabled': return [
                'type' => 'enabled',
                'value' => true,
            ];
            case 'family': return [
                'type' => 'family',
                'operator' => $operator ?? 'IN',
                'value' => ['tshirt'],
            ];
            case 'simple_select': return [
                'type' => 'simple_select',
                'operator' => $operator ?? 'IN',
                'attributeCode' => 'color',
                'value' => ['green'],
            ];
            case 'multi_select': return [
                'type' => 'multi_select',
                'operator' => $operator ?? 'IN',
                'attributeCode' => 'a_multi_select',
                'value' => ['option_a', 'option_b'],
            ];
        }

        throw new \InvalidArgumentException('Unknown type ' . $type . ' for getValidCondition');
    }

    /**
     * @return string[]
     */
    private function splitList(string $codesList): array
    {
        $codesWithQuotes = \preg_split('/(, )|( and )/', $codesList);

        return \array_map(
            static fn (string $codeWithQuotes): string => \substr($codeWithQuotes, 1, -1),
            $codesWithQuotes
        );
    }
}
