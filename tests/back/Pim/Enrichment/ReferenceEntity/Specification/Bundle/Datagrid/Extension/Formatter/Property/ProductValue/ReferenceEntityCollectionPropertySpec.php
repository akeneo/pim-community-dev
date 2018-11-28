<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\Datagrid\Extension\Formatter\Property\ProductValue;

use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Request\RequestParametersExtractorInterface;
use PhpSpec\ObjectBehavior;

/**
 * Datagrid column formatter for a reference entity or a reference entity collection
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ReferenceEntityCollectionPropertySpec extends ObjectBehavior
{
    function let(
        \Twig_Environment $environment,
        RequestParametersExtractorInterface $paramsExtractor,
        UserContext $userContext
    ) {
        $this->beConstructedWith($environment, $paramsExtractor, $userContext);

        $params = new PropertyConfigurationFake(['name' => 'foo']);
        $this->init($params);
    }

    function it_is_a_property()
    {
        $this->shouldImplement(PropertyInterface::class);
    }

    function it_formats_a_string_value(ResultRecordInterface $record)
    {
        $value = ['data' => 'Tony Stark'];
        $expectedFormattedValue = 'Tony Stark';

        $record->getValue('[values][foo]')->willReturn([$value]);

        $this->getValue($record)->shouldReturn($expectedFormattedValue);
    }

    function it_formats_array_values(ResultRecordInterface $record)
    {
        $value = ['data' => ['Tony Stark', 'Bruce Banner']];
        $expectedFormattedValue = 'Tony Stark, Bruce Banner';

        $record->getValue('[values][foo]')->willReturn([$value]);

        $this->getValue($record)->shouldReturn($expectedFormattedValue);
    }
}

class PropertyConfigurationFake extends PropertyConfiguration
{
    public function __construct(array $params)
    {
        $this->params = $params;
    }
}
