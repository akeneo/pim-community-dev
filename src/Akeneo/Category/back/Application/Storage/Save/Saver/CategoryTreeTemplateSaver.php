<?php

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Domain\Model\Template;

interface CategoryTreeTemplateSaver
{
    public function insert(Template $templateModel): void;

    public function update(Template $templateModel): void;
}
