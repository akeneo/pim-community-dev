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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Converter;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionsMapping
    as FranklinAttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Converter\AttributeOptionsMappingConverter;
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
        $filepath = realpath(FakeClient::FAKE_PATH) . '/mapping/router/attributes/color/options.json';
        $mappingData = json_decode(file_get_contents($filepath), true);
        $clientMapping = new FranklinAttributeOptionsMapping($mappingData['mapping']);

        $pimAttributeOptionsMapping = $this
            ->clientToApplication(new FamilyCode('family_code'), 'franklin_id', $clientMapping);
        $pimAttributeOptionsMapping->shouldReturnAnInstanceOf(AttributeOptionsMapping::class);
    }
}
