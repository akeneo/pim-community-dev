<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Application\Command\CreateTemplate;

use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommand;
use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommandHandler;
use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Test\Integration\Configuration;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateTemplateCommandHandlerIntegration extends CategoryTestCase
{
    private CreateTemplateCommandHandler $createTemplateCommandHandler;
    private GetCategoryInterface $getCategory;
    private GetCategoryTemplateByCategoryTree $getTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTemplateCommandHandler = $this->get(CreateTemplateCommandHandler::class);
        $this->getCategory = $this->get(GetCategoryInterface::class);
        $this->getTemplate = $this->get(GetCategoryTemplateByCategoryTree::class);
    }

    public function testItCreatesATemplateInDatabase(): void
    {
        /** @var Category $masterCategory */
        $masterCategory = $this->getCategory->byCode('master');

        $createTemplateCommand = new CreateTemplateCommand(
            $masterCategory->getId(),
            [
                'code' => 'master_template',
                'labels' => [
                    'en_US' => 'e-commerce',
                ],
            ]
        );

        $this->createTemplateCommandHandler->__invoke($createTemplateCommand);

        $template = $this->getTemplate->__invoke($masterCategory->getId());

        $this->assertEquals('master_template', $template->getCode());
        $this->assertEquals('e-commerce', $template->getLabelCollection()->getTranslation('en_US'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
