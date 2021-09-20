<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\AttributeGroup;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\AttributeGroup\GetAttributeGroupCodesAndLabels;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

class GetAttributeGroupCodesAndLabelsIntegration extends TestCase
{
    private UserInterface $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createAdminUser();

        $this->createAttributeGroup('marketing', ['labels' => ['en_US' => 'Marketing']]);
        $this->createAttributeGroup('technical', ['labels' => ['fr_FR' => 'Technique']]);
    }

    public function test_it_gets_paginated_attribute_groups(): void
    {
        $expectedFirstPage = [
            [
                'code' => 'marketing',
                'label' => 'Marketing',
            ],
            [
                'code' => 'other',
                'label' => 'Other',
            ]
        ];
        $firstPage = $this->getQuery()->execute(
            $this->user->getUiLocale()->getCode(),
            '',
            0,
            2
        );

        $this->assertEqualsCanonicalizing($expectedFirstPage, $firstPage);

        $expectedSecondPage = [
            [
                'code' => 'technical',
                'label' => null,
            ]
        ];
        $secondPage = $this->getQuery()->execute(
            $this->user->getUiLocale()->getCode(),
            '',
            2,
            2
        );

        $this->assertEqualsCanonicalizing($expectedSecondPage, $secondPage);
    }

    public function test_it_searches_for_an_attribute_group(): void
    {
        $this->createAttributeGroup('technical2', ['labels' => ['en_US' => 'Technical2']]);
        $this->createAttributeGroup('technical3', ['labels' => ['en_US' => 'Technical3']]);
        $this->createAttributeGroup('technical4', ['labels' => ['en_US' => 'Technical4']]);
        $search = 'echnic';
        $expectedFirstPage = [
            [
                'code' => 'technical',
                'label' => null,
            ],
            [
                'code' => 'technical2',
                'label' => 'Technical2',
            ],
        ];
        $firstPageResult = $this->getQuery()->execute(
            $this->user->getUiLocale()->getCode(),
            $search,
            0,
            2
        );

        $this->assertEqualsCanonicalizing($expectedFirstPage, $firstPageResult);

        $expectedSecondPage = [
            [
                'code' => 'technical3',
                'label' => 'Technical3',
            ],
            [
                'code' => 'technical4',
                'label' => 'Technical4',
            ],
        ];
        $expectedSecondResult = $this->getQuery()->execute(
            $this->user->getUiLocale()->getCode(),
            $search,
            2,
            2
        );
        $this->assertEqualsCanonicalizing($expectedSecondPage, $expectedSecondResult);
    }

    private function createAttributeGroup(string $code, array $data = []): AttributeGroupInterface
    {
        $data = array_merge(['code' => $code], $data);

        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $data);
        $violations = $this->get('validator')->validate($attributeGroup);
        Assert::assertSame(0, $violations->count());
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        return $attributeGroup;
    }

    private function getQuery(): GetAttributeGroupCodesAndLabels
    {
        return $this->get(GetAttributeGroupCodesAndLabels::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
