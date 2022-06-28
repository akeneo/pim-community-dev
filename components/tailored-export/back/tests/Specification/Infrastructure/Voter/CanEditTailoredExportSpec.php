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

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
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
                        'code' => 'Weight',
                        'type' => 'attribute',
                        'locale' => null
                    ]
                ]
            ]
        ]
    ];

    public function let(
        FindAllViewableLocalesForUser $findAllViewableLocalesForUser,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith($findAllViewableLocalesForUser, $getViewableAttributes, $getAttributes);
    }

    public function it_does_not_validate_if_columns_is_not_defined(JobInstance $jobInstance): void
    {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn([]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    public function it_does_not_validate_if_an_attribute_is_not_visible(
        GetAttributes $getAttributes,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        JobInstance $jobInstance
    ): void {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn(self::COLUMNS);

        $description = $this->createAttribute('description');
        $name = $this->createAttribute('name');
        $Weight = $this->createAttribute('Weight');

        $getAttributes->forCodes(['description', 'name', 'Weight'])->willReturn([
            'description' => $description,
            'name' => $name,
            'Weight' => $Weight
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'name', 'Weight'], $userId)->willReturn([
            'description',
            'name'
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    public function it_validates_if_an_attribute_is_deleted(
        GetAttributes $getAttributes,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        FindAllViewableLocalesForUser $findAllViewableLocalesForUser,
        JobInstance $jobInstance
    ): void {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn(self::COLUMNS);

        $description = $this->createAttribute('description');
        $Weight = $this->createAttribute('Weight');

        $getAttributes->forCodes(['description', 'name', 'Weight'])->willReturn([
            'description' => $description,
            'name' => null,
            'Weight' => $Weight
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'Weight'], $userId)->willReturn([
            'description',
            'Weight'
        ]);

        $findAllViewableLocalesForUser->findAll($userId)->willReturn([
            new Locale('fr_FR', true),
            new Locale('en_US', true),
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(true);
    }

    public function it_does_not_validate_if_a_locale_is_not_visible(
        GetAttributes $getAttributes,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        FindAllViewableLocalesForUser $findAllViewableLocalesForUser,
        JobInstance $jobInstance
    ): void {
        $userId = 2;
        $jobInstance->getRawParameters()->willReturn(self::COLUMNS);

        $description = $this->createAttribute('description');
        $Weight = $this->createAttribute('Weight');

        $getAttributes->forCodes(['description', 'name', 'Weight'])->willReturn([
            'description' => $description,
            'Weight' => $Weight
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'Weight'], $userId)->willReturn([
            'description',
            'Weight'
        ]);

        $findAllViewableLocalesForUser->findAll($userId)->willReturn([
            new Locale('en_US', true),
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    private function createAttribute(string $attributeCode): Attribute
    {
        return new Attribute($attributeCode, '', [], false, false, null, null, false, '', []);
    }
}
