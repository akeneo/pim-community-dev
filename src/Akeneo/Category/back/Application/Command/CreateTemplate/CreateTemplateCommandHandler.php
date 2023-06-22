<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CreateTemplate;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\Domain\Exception\CategoryTreeNotFoundException;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateTemplateCommandHandler
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly GetCategoryInterface $getCategory,
        private readonly GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree,
        private readonly CategoryTemplateSaver $categoryTemplateSaver,
        private readonly CategoryTreeTemplateSaver $categoryTreeTemplateSaver,
    ) {
    }

    public function __invoke(CreateTemplateCommand $command): void
    {
        $categoryTreeId = $command->categoryTreeId;
        $templateCode = TemplateCode::fromString($command->templateCode);
        $templateLabelCollection = LabelCollection::fromArray($command->labels);

        $categoryTree = $this->getCategory->byId($categoryTreeId->getValue());
        if ($categoryTree === null) {
            throw new CategoryTreeNotFoundException();
        }

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        if (!$this->validateTemplateCreation($categoryTree, $templateCode)) {
            throw new \RuntimeException(\sprintf("Template for category tree '%s' cannot be activated.", $categoryTree->getCode()));
        }
        $templateToSave = Template::create(
            $categoryTreeId,
            $templateCode,
            $templateLabelCollection,
        );

        $this->categoryTemplateSaver->insert($templateToSave);
        $this->categoryTreeTemplateSaver->insert($templateToSave);
    }

    /**
     * A template creation is considered valid if:
     *  - the current category tree has no template attached
     *  - the attached category id is the root of a category tree.
     *
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function validateTemplateCreation(Category $categoryTree, TemplateCode $templateCode): bool
    {
        if (($this->getCategoryTemplateByCategoryTree)($categoryTree->getId())) {
            return false;
        }

        if ($categoryTree->getParentId() !== null) {
            return false;
        }

        return true;
    }
}
