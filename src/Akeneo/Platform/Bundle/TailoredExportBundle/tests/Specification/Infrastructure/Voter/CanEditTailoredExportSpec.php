<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Voter;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;


class CanEditTailoredExportSpec extends ObjectBehavior
{
    const COLUMNS = [
        'columns' => [
            [
                'sources' => [
                    [
                        'code' => 'description',
                        'type' => 'attribute',
                        'locale' => null
                    ],
                    [
                        'code' => 'name',
                        'type' => 'attribute',
                        'locale' => 'fr_FR'
                    ],
                    [
                        'code' => 'status',
                        'type' => 'property',
                        'locale' => null
                    ]
                ]
            ],
            [
                'sources' => [
                    [
                        'code' => 'weight',
                        'type' => 'attribute',
                        'locale' => null
                    ]
                ]
            ]
        ]
    ];

    public function let(
        GetAllViewableLocalesForUserInterface $getAllViewableLocales,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith($getAllViewableLocales, $getViewableAttributes, $getAttributes);
    }

    public function it_does_not_validate_if_columns_is_not_defined(JobInstance $jobInstance): void
    {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn([]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    public function it_does_not_validate_if_an_attribute_is_not_visible(
        $getAttributes,
        $getViewableAttributes,
        JobInstance $jobInstance
    ): void {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn(self::COLUMNS);

        $description = $this->createAttribute('description');
        $name = $this->createAttribute('name');
        $weight = $this->createAttribute('weight');

        $getAttributes->forCodes(['description', 'name', 'weight'])->willReturn([
            $description,
            $name,
            $weight
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'name', 'weight'], $userId)->willReturn([
            'description',
            'name'
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    public function it_validates_if_an_attribute_is_deleted(
        $getAttributes,
        $getViewableAttributes,
        $getAllViewableLocales,
        JobInstance $jobInstance
    ): void {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn(self::COLUMNS);

        $description = $this->createAttribute('description');
        $weight = $this->createAttribute('weight');

        $getAttributes->forCodes(['description', 'name', 'weight'])->willReturn([
            $description,
            $weight
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'weight'], $userId)->willReturn([
            'description',
            'weight'
        ]);

        $getAllViewableLocales->fetchAll($userId)->willReturn([
            'fr_FR',
            'en_US'
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(true);
    }

    public function it_does_not_validate_if_a_locale_is_not_visible(
        $getAttributes,
        $getViewableAttributes,
        $getAllViewableLocales,
        JobInstance $jobInstance
    ): void {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn(self::COLUMNS);

        $description = $this->createAttribute('description');
        $weight = $this->createAttribute('weight');

        $getAttributes->forCodes(['description', 'name', 'weight'])->willReturn([
            $description,
            $weight
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'weight'], $userId)->willReturn([
            'description',
            'weight'
        ]);

        $getAllViewableLocales->fetchAll($userId)->willReturn([
            'en_US'
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    private function createAttribute(string $attributeCode): Attribute
    {
        return new Attribute($attributeCode, '', [], false, false, null, null, false, '', []);
    }
}
