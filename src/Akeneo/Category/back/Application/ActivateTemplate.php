<?php

namespace Akeneo\Category\Application;

use Akeneo\Category\Application\Query\GetCategoryTemplateByCategoryTree;
use Akeneo\Category\Application\Query\GetCategoryTreeByCategoryTemplate;
use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\Domain\Model\Category;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Infrastructure\Builder\TemplateBuilder;

/**
 * Validate the new static category template data and save the template in database
 */
class ActivateTemplate
{
    public function __construct(
        private GetTemplate $getTemplate,
        private GetCategoryInterface $getCategory,
        private GetCategoryTemplateByCategoryTree $getCategoryTemplateByCategoryTree,
        private GetCategoryTreeByCategoryTemplate $getCategoryTreeByCategoryTemplate,
        private TemplateBuilder $templateBuilder,
        private CategoryTemplateSaver $categoryTemplateSaver,
        private CategoryTreeTemplateSaver $categoryTreeTemplateSaver,
    ) {
    }

    /**
     * @param CategoryId $categoryTreeId
     * @param TemplateCode $templateCode
     * @param LabelCollection $templateLabelCollection
     * @return Template|null
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(
        CategoryId $categoryTreeId,
        TemplateCode $templateCode,
        LabelCollection $templateLabelCollection
    ): ?Template {
        $categoryTree = $this->getCategory->byId($categoryTreeId->getValue());

        if ($categoryTree === null) {
            throw new \Exception(sprintf("Category tree not found. Id: %d", $categoryTreeId->getValue()));
        }

        if (!$this->validateTemplateActivation($categoryTree, $templateCode)) {
            throw new \Exception(
                \sprintf("Template for category tree '%s' cannot be activated.", $categoryTree->getCode())
            );
        }

        $templateToSave = $this->templateBuilder->generateTemplate(
            $categoryTree->getId(),
            $templateCode,
            $templateLabelCollection
        );

        return $this->activateTemplate($templateToSave);
    }

    /**
     * A template activation is considered valid if:
     *  - the current category tree has no template attached
     *  - the attached category id is the root of a category tree
     *  - the template code is not already in use
     * @param Category $categoryTree
     * @param TemplateCode $templateCode
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function validateTemplateActivation(Category $categoryTree, TemplateCode $templateCode): bool
    {
        if (($this->getCategoryTemplateByCategoryTree)($categoryTree->getId())) {
            return false;
        }

        if ($categoryTree->getParentId() !== null) {
            return false;
        }

        if ($this->getTemplate->exists($templateCode)) {
            return false;
        }

        return true;
    }

    /**
     * @param Template $templateModel
     * @return Template
     */
    private function activateTemplate(Template $templateModel): Template
    {
        $this->categoryTemplateSaver->insert($templateModel);

        if (($this->getCategoryTreeByCategoryTemplate)($templateModel->getUuid()) === null) {
            $this->categoryTreeTemplateSaver->insert($templateModel);
        }

        return $this->getTemplate->byUuid((string) $templateModel->getUuid());
    }
}
