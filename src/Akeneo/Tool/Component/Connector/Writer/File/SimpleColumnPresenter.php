<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer\File;

class SimpleColumnPresenter implements ColumnPresenterInterface
{
    public function present(array $data, array $context): array
    {
        return \array_combine($data, $data);
    }
}
