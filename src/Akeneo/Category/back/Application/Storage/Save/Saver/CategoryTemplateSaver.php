<?php

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Domain\Model\Template;

interface CategoryTemplateSaver
{
    public function insert(Template $templateModel);

    public function update(Template $templateModel);
}
