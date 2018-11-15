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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping
    as FranklinAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter\AttributeOptionsMappingConverter;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingConverterSpec extends ObjectBehavior
{
    public function it_is_an_attribute_options_mapping_converter(): void
    {
        $this->shouldHaveType(AttributeOptionsMappingConverter::class);
    }

    public function it_converts_a_client_attribute_options_mapping_into_application_model(): void
    {
        $fakeDirectory = realpath(__DIR__ . '/../../../../Resources/fake/franklin-api/attribute-options-mapping');
        $filename = 'get_family_router_attribute_color.json';
        $mappingData = json_decode(file_get_contents(sprintf('%s/%s', $fakeDirectory, $filename)), true);
        $clientMapping = new FranklinAttributeOptionsMapping($mappingData);

        $pimAttributeOptionsMapping = $this
            ->clientToApplication('family_code', 'franklin_id', $clientMapping);
        $pimAttributeOptionsMapping->shouldReturnAnInstanceOf(AttributeOptionsMapping::class);
    }
}
