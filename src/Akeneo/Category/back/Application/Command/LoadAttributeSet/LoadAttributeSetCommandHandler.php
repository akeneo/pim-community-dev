<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\LoadAttributeSet;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\AttributeSetFactory;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LoadAttributeSetCommandHandler
{
    public function __construct(
        private readonly GetTemplate $getTemplate,
        private readonly GetAttribute $getAttribute,
        private readonly AttributeSetFactory $attributeSetFactory,
        private readonly CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver,
    ) {
    }

    public function __invoke(LoadAttributeSetCommand $command): void
    {
        $template = $this->getTemplate->byUuid(TemplateUuid::fromString($command->templateUuid));
        $attributeCollection = $this->getAttribute->byTemplateUuid($template->getUuid());
        if ($attributeCollection->count() > 0) {
            return;
        }

        $attributeCollection = $this->attributeSetFactory->createAttributeCollection($template->getUuid());

        $this->categoryTemplateAttributeSaver->insert($template->getUuid(), $attributeCollection);
    }
}
