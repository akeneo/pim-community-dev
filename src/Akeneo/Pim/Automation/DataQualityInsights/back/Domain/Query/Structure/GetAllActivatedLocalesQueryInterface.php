<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;

interface GetAllActivatedLocalesQueryInterface
{
    public function execute(): LocaleCollection;

    public function clearCache(): void;
}
