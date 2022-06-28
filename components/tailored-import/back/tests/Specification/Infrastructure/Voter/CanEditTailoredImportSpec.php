<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Voter;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;

class CanEditTailoredImportSpec extends ObjectBehavior
{
    private const JOB_RAW_PARAMETERS = ['import_structure' => ['data_mappings' => [
        [
            'target' => [
                'code' => 'description',
                'locale' => 'fr_FR',
                'type' => 'attribute',
            ],
        ],
        [
            'target' => [
                'code' => 'description',
                'locale' => 'en_US',
                'type' => 'attribute',
            ],
        ],
        [
            'target' => [
                'code' => 'description',
                'locale' => null,
                'type' => 'attribute',
            ],
        ],
        [
            'target' => [
                'code' => 'name',
                'locale' => 'fr_FR',
                'type' => 'attribute',
            ],
        ],
        [
            'target' => [
                'code' => 'name',
                'locale' => null,
                'type' => 'attribute',
            ],
        ],
        [
            'target' => [
                'code' => 'Weight',
                'locale' => 'fr_FR',
                'type' => 'attribute',
            ],
        ],
        [
            'target' => [
                'code' => 'family',
                'locale' => null,
                'type' => 'property',
            ],
        ],
    ]]];

    public function let(
        FindAllViewableLocalesForUser $findAllViewableLocalesForUser,
        GetViewableAttributeCodesForUserInterface $getViewableAttributes,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith($findAllViewableLocalesForUser, $getViewableAttributes, $getAttributes);
    }

    public function it_does_not_validate_if_data_mappings_are_not_defined(JobInstance $jobInstance): void
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
        $jobInstance->getRawParameters()->willReturn(self::JOB_RAW_PARAMETERS);

        $description = $this->createAttribute('description');
        $name = $this->createAttribute('name');
        $weight = $this->createAttribute('Weight');

        $getAttributes->forCodes(['description', 'name', 'Weight'])->willReturn([
            'description' => $description,
            'name' => $name,
            'Weight' => $weight,
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'name', 'Weight'], $userId)->willReturn([
            'description',
            'name',
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
        $jobInstance->getRawParameters()->willReturn(self::JOB_RAW_PARAMETERS);

        $description = $this->createAttribute('description');
        $weight = $this->createAttribute('Weight');

        $getAttributes->forCodes(['description', 'name', 'Weight'])->willReturn([
            'description' => $description,
            'name' => null,
            'Weight' => $weight,
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'Weight'], $userId)->willReturn([
            'description',
            'Weight',
        ]);

        $findAllViewableLocalesForUser->findAll($userId)->willReturn([
            new Locale('fr_FR', true),
            new Locale('en_US', false),
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
        $jobInstance->getRawParameters()->willReturn(self::JOB_RAW_PARAMETERS);

        $description = $this->createAttribute('description');
        $weight = $this->createAttribute('Weight');

        $getAttributes->forCodes(['description', 'name', 'Weight'])->willReturn([
            'description' => $description,
            'Weight' => $weight,
        ]);

        $getViewableAttributes->forAttributeCodes(['description', 'Weight'], $userId)->willReturn([
            'description',
            'Weight',
        ]);

        $findAllViewableLocalesForUser->findAll($userId)->willReturn([
            new Locale('en_US', false),
        ]);

        $this->execute($jobInstance, $userId)->shouldReturn(false);
    }

    private function createAttribute(string $attributeCode): Attribute
    {
        return new Attribute($attributeCode, '', [], false, false, null, null, false, '', []);
    }
}
