<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Converter\Stub;

use Oro\Bundle\ImportExportBundle\Converter\QueryBuilderAwareInterface;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;

interface QueryBuilderAwareDataConverter extends QueryBuilderAwareInterface, DataConverterInterface
{
}
