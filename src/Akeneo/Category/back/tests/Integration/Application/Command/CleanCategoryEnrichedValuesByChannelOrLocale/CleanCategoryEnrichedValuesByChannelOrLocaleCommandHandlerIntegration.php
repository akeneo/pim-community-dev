<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale;

use Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale\CleanCategoryEnrichedValuesByChannelOrLocaleCommand;
use Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByChannelOrLocale\CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandler;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Query\GetCategoryInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandlerIntegration extends CategoryTestCase
{
    public function testItCleansValueCollectionOnChannelDeletion(): void
    {
        $categorySocks = $this->useTemplateFunctionalCatalog(
            '6344aa2a-2be9-4093-b644-259ca7aee50c',
            'socks'
        );

        $this->createChannel(
            'testChannelDeletion',
            [
            'locales' => ['en_US', 'fr_FR'],
            'currencies' => ['USD'],
            'category_tree' => 'socks',
            ]
        );

        $this->updateCategoryWithValues((string) $categorySocks->getCode(),'testChannelDeletion');

        $command = new CleanCategoryEnrichedValuesByChannelOrLocaleCommand('testChannelDeletion', []);
        $commandHandler = $this->get(CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandler::class);
        ($commandHandler)($command);

        $getCategory = $this->get(GetCategoryInterface::class);
        $category = $getCategory->byCode('socks');

        foreach ($category->getAttributes()->getValues() as $value)
        {
            $this->assertNotEquals('testChannelDeletion',(string) $value->getChannel());
        }
    }

    public function testItCleansValueCollectionOnLocaleDeletion(): void
    {
        $categorySocks = $this->useTemplateFunctionalCatalog(
            '6344aa2a-2be9-4093-b644-259ca7aee50c',
            'socks'
        );

        $this->createChannel(
            'testChannelDeletion',
            [
                'locales' => ['en_US', 'fr_FR'],
                'currencies' => ['USD'],
                'category_tree' => 'socks',
            ]
        );

        $this->updateCategoryWithValues((string) $categorySocks->getCode(),'testChannelDeletion');

        $command = new CleanCategoryEnrichedValuesByChannelOrLocaleCommand('testChannelDeletion', ['en_US']);
        $commandHandler = $this->get(CleanCategoryEnrichedValuesByChannelOrLocaleCommandHandler::class);
        ($commandHandler)($command);

        $getCategory = $this->get(GetCategoryInterface::class);
        $category = $getCategory->byCode('socks');

        $this->assertNull($category->getAttributes()->getValue(
            'title',
            '87939c45-1d85-4134-9579-d594fff65030',
            'testChannelDeletion',
            'fr_FR'
        ));

        $this->assertNotNull($category->getAttributes()->getValue(
            'description',
            '57665726-8a6e-4550-9bcf-06f81c0d1e24',
            'ecommerce',
            'fr_FR'
        ));
    }
}
