<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\DeleteCategoryCommand;

use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByTemplateUuid;
use Akeneo\Category\Domain\Query\GetCategoryTreeTemplates;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCategoryCommandHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly RemoverInterface $remover,
        private readonly GetCategoryTreeTemplates $getCategoryTreeTemplates,
        private readonly DeleteCategoryTreeTemplateByTemplateUuid $deleteCategoryTreeTemplateByTemplateUuid,
    ) {
    }

    public function __invoke(DeleteCategoryCommand $command): void
    {
        $category = $this->categoryRepository->find($command->id);
        if (null === $category) {
            return;
        }

        if ($category->isRoot()) {
            $templateUuids = ($this->getCategoryTreeTemplates)(new CategoryId($command->id));
            foreach ($templateUuids as $templateUuid) {
                ($this->deleteCategoryTreeTemplateByTemplateUuid)($templateUuid);
            }
        }

        $this->remover->remove($category);
    }
}
