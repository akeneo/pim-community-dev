<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Akeneo\Test\Acceptance\Category\InMemoryCategoryRepository;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StructureContext implements Context
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly AttributeOptionRepositoryInterface $attributeOptionRepository,
        private readonly FindFamilyCodes $findFamilyCodes,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
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
     * @Given /^the (?P<optionCodes>(('.*')(, | and )?)+) options? for '(?P<attributeCode>[^']*)' attribute$/
     */
    public function theAndOptionsForAttribute(string $optionCodes, string $attributeCode): void
    {
        foreach (CodesSplitter::split($optionCodes) as $optionCode) {
            $attributeOption = new AttributeOption();
            $attributeOption->setCode($optionCode);
            $attributeOption->setAttribute($this->attributeRepository->findOneByIdentifier($attributeCode));
            $this->attributeOptionRepository->save($attributeOption);
        }
    }

    /**
     * @Given the :familyCode family
     */
    public function theFamily(string $familyCode): void
    {
        $this->findFamilyCodes->save($familyCode);
    }

    /**
     * @Given /^the '(?P<channelCode>[^']*)' channel having (?P<localeCodes>(('.*')(, | and )?)+) as locales?$/
     */
    public function theChannelHavingActiveLocalesAnd(string $channelCode, string $localeCodes): void
    {
        $channel = new Channel();
        $channel->setCode($channelCode);
        $locales = [];
        foreach (CodesSplitter::split($localeCodes) as $localeCode) {
            $locale = new Locale();
            $locale->setCode($localeCode);
            $locale->addChannel($channel);
            $locales[] = $locale;
        }
        $channel->setLocales($locales);

        $this->channelRepository->save($channel);
    }

    /**
     * @Given /^the (?P<categoryCodes>(('.*')(, | and )?)+) categories$/
     */
    public function theCategories(string $categoryCodes): void
    {
        Assert::isInstanceOf($this->categoryRepository, InMemoryCategoryRepository::class);
        foreach (CodesSplitter::split($categoryCodes) as $categoryCode) {
            $category = new Category();
            $category->setCode($categoryCode);
            $this->categoryRepository->save($category);
        }
    }
}
