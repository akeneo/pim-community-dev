<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

interface ColumnPresenterInterface
{
    public function present(array $data, array $context): array;
}
