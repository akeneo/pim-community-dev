<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\SharedCatalog\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductCsvExport;

class DefaultValueProvider extends ProductCsvExport
{
    public function getDefaultValues()
    {
        $parameters = parent::getDefaultValues();

        $parameters['publisher'] = null;
        $parameters['recipients'] = [];
        $parameters['branding'] = [
            'image' => null,
        ];

        return $parameters;
    }
}
