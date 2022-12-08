<?php

namespace Akeneo\CoEdition\Infrastructure\Storage;

use Akeneo\CoEdition\Application\Storage\FindEditor;
use Akeneo\CoEdition\Domain\Editor;

class PimUserFindEditor implements FindEditor
{

    public function __invoke(string $editorId): ?Editor
    {
        // TODO: Implement __invoke() method.
    }
}
