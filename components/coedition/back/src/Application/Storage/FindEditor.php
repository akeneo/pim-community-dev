<?php

namespace Akeneo\CoEdition\Application\Storage;

use Akeneo\CoEdition\Domain\Editor;

interface FindEditor
{
    public function __invoke(string $editorId): ?Editor;

}
