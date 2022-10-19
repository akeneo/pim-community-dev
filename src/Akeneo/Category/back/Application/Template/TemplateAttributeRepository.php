<?php

namespace Akeneo\Category\Application\Template;

use Akeneo\Category\Domain\Model\Template;

interface TemplateAttributeRepository
{
    public function insert(Template $templateModel);

    public function update(Template $templateModel);
}
