<?php

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Domain\Model\Enrichment\Template;

interface CategoryTemplateSaver
{
    public function insert(Template $templateModel): void;

    public function update(Template $templateModel): void;
}
