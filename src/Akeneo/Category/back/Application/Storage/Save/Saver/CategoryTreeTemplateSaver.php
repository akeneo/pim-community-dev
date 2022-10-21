<?php

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Domain\Model\Template;

interface CategoryTreeTemplateSaver
{
    public function insert(Template $templateModel);

    public function update(Template $templateModel);

    public function linkAlreadyExists($templateModel): bool;
}
