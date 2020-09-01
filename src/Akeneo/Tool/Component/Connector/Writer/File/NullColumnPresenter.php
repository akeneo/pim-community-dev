<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

class NullColumnPresenter implements ColumnPresenterInterface
{
    public function present(array $data, array $context): array
    {
        return $data;
    }
}
