<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\UpdateTemplateCommand;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateTemplateCommandHandler
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly GetTemplate $getTemplate,
        private readonly CategoryTemplateSaver $categoryTemplateSaver,
    ) {
    }

    public function __invoke(UpdateTemplateCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        $template = $this->getTemplate->byUuid(TemplateUuid::fromString($command->templateUuid));

        $template->update(
            labelCollection: LabelCollection::fromArray($command->labels),
        );
        $this->categoryTemplateSaver->update($template);
    }
}
