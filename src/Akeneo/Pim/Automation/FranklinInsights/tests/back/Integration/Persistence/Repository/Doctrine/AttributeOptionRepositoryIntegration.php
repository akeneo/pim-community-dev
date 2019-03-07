<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as PimAttribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption as PimAttributeOption;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class AttributeOptionRepositoryIntegration extends TestCase
{
    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeOptionSaver;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $attributeSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeOptionRepository = $this->getFromTestContainer(
            'akeneo.pim.automation.franklin_insights.repository.attribute_option'
        );
        $this->attributeBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->getFromTestContainer('pim_catalog.saver.attribute');
        $this->attributeOptionSaver = $this->getFromTestContainer('pim_catalog.saver.attribute_option');
        $this->validator = $this->getFromTestContainer('validator');
    }

    public function test_it_finds_an_attribute_option_by_identifier(): void
    {
        $attributeOption = $this->attributeOptionRepository->findOneByIdentifier('router.color');
        $this->assertNull($attributeOption);

        $attribute = $this->createAttribute('router');
        $this->createAttributeOption('color', $attribute);
        $this->createAttributeOption('size', $attribute);

        $attributeOption = $this->attributeOptionRepository->findOneByIdentifier('router.color');
        $expectedAttributeOption = new AttributeOption('color', new AttributeCode('router'));
        $this->assertEquals($attributeOption, $expectedAttributeOption);
    }

    public function test_it_finds_attribute_option_codes_for_an_attribute(): void
    {
        $attributeOptionCodes = $this->attributeOptionRepository->findCodesByIdentifiers('router', ['color', 'size']);
        $this->assertEmpty($attributeOptionCodes);

        $attribute = $this->createAttribute('router');
        $this->createAttributeOption('color', $attribute);
        $this->createAttributeOption('size', $attribute);
        $this->createAttributeOption('width', $attribute);
        $this->createAttributeOption('color', $this->createAttribute('shoes'));

        $attributeOptionCodes = $this->attributeOptionRepository->findCodesByIdentifiers('router', ['color', 'size']);
        $expectedAttributeOptionCodes = ['color', 'size'];
        $this->assertEquals($attributeOptionCodes, $expectedAttributeOptionCodes);
    }

    public function test_it_finds_attribute_options_by_codes(): void
    {
        $attributeOptions = $this->attributeOptionRepository->findByCode(['color']);
        $this->assertEmpty($attributeOptions);

        $attribute = $this->createAttribute('router');
        $this->createAttributeOption('color', $attribute);
        $this->createAttributeOption('size', $attribute);
        $this->createAttributeOption('color', $this->createAttribute('shoes'));
        $this->createAttributeOption('size', $this->createAttribute('bike'));

        $attributeOptions = $this->attributeOptionRepository->findByCode(['color']);
        $expectedAttributeOptions = [
            new AttributeOption('color', new AttributeCode('router')),
            new AttributeOption('color', new AttributeCode('shoes')),
        ];
        $this->assertEquals($attributeOptions, $expectedAttributeOptions);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributeOption(string $attributeOptionCode, PimAttribute $attribute): PimAttributeOption
    {
        $attributeOption = new PimAttributeOption();
        $attributeOption->setCode($attributeOptionCode);
        $attributeOption->setAttribute($attribute);

        $this->attributeOptionSaver->save($attributeOption);

        return $attributeOption;
    }

    private function createAttribute(string $attributeCode): PimAttribute
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );

        $this->attributeSaver->save($attribute);

        return $attribute;
    }
}
