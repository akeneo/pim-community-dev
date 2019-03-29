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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class FamilyRepositoryIntegration extends TestCase
{
    private const TEST_FAMILY_CODE = 'test_family';
    private const TEST_FAMILY_LABELS = ['en_US' => 'A family for testing purpose'];

    private const CONTROL_FAMILY_CODE = 'control_family';
    private const CONTROL_FAMILY_LABELS = ['en_US' => 'A control family'];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $testFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => self::TEST_FAMILY_CODE,
            'labels' => self::TEST_FAMILY_LABELS,
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($testFamily);

        $controlFamily = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.family')->build([
            'code' => self::CONTROL_FAMILY_CODE,
            'labels' => self::CONTROL_FAMILY_LABELS,
            'attributes' => ['sku'],
        ]);
        $this->getFromTestContainer('validator')->validate($controlFamily);

        $this
            ->getFromTestContainer('pim_catalog.saver.family')
            ->saveAll([$testFamily, $controlFamily]);
    }

    public function test_find_a_family_from_its_identifier()
    {
        $familyCode = new FamilyCode(self::TEST_FAMILY_CODE);
        $expectedFamily = new Family($familyCode, self::TEST_FAMILY_LABELS);
        $family = $this->getRepository()->findOneByIdentifier($familyCode);

        $this->assertEquals($expectedFamily, $family);

        $unknownFamily = $this->getRepository()->findOneByIdentifier(new FamilyCode('foo'));
        $this->assertNull($unknownFamily);
    }

    public function test_check_that_a_family_exist()
    {
        $familyExist = $this->getRepository()->exist(new FamilyCode(self::TEST_FAMILY_CODE));
        $this->assertTrue($familyExist);

        $familyExist = $this->getRepository()->exist(new FamilyCode('foo'));
        $this->assertFalse($familyExist);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return FamilyRepositoryInterface
     */
    private function getRepository(): FamilyRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.franklin_insights.repository.family');
    }
}
