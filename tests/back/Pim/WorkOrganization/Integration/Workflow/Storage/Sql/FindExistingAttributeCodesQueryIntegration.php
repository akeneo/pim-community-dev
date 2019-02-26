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

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Storage\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FindExistingAttributeCodesQueryIntegration extends TestCase
{
    public function testExistingAttributeCodes()
    {
        $attribute = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')
            ->build([
            'code' => 'attribute_1',
            'type' => AttributeTypes::TEXT
        ]);

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);

        $existingCodes = $this
            ->getFromTestContainer('pimee_workflow.sql.product.find_existing_attribute_codes')
            ->execute(['attribute_1', 'attribute_2', 'attribute_3', 'attribute_4']);

        Assert::assertCount(1, $existingCodes);
        Assert::assertSame(['attribute_1'], $existingCodes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
