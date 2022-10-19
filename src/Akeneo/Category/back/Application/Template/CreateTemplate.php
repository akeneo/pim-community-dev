<?php

namespace Akeneo\Category\Application\Template;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Template;

/**
 * Create a category template in database
 */
class CreateTemplate
{
    public function __construct(
        private GetTemplate $getTemplate,
        private TemplateRepository $templateRepository,
        private CategoryTreeTemplateRepository $categoryTreeTemplateRepository,
    ) {
    }

    public function __invoke(Template $templateModel): void {
        if (null === $this->getTemplate->byUuid($templateModel->getUuid())) {
            $this->templateRepository->insert($templateModel);
        } else {
            return;
        }

        if (!$this->categoryTreeTemplateRepository->linkAlreadyExists($templateModel)) {
            $this->categoryTreeTemplateRepository->insert($templateModel);
        }
    }
}
