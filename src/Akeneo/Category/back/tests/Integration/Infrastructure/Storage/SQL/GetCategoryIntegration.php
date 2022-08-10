<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\SQL;

use Akeneo\Category\Application\Query\FindCategoryByIdentifier;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Infrastructure\Storage\SQL\GetCategory;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryIntegration extends TestCase
{
    public function testGetCategoryByCode(): void
    {
        $category = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $category = $this->get(GetCategoryInterface::class)->byCode($category->getCode());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('Chaussettes', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Socks', $category->getLabelCollection()->getLabel('en_US'));
    }

    public function testGetCategoryById(): void
    {
        $category = $this->createCategory([
            'code' => 'pants',
            'labels' => [
                'fr_FR' => 'Pantalons',
                'en_US' => 'Pants'
            ]
        ]);

        $category = $this->get(GetCategoryInterface::class)->byId($category->getId());
        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('pants', (string)$category->getCode());
        $this->assertSame('Pantalons', $category->getLabelCollection()->getLabel('fr_FR'));
        $this->assertSame('Pants', $category->getLabelCollection()->getLabel('en_US'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
