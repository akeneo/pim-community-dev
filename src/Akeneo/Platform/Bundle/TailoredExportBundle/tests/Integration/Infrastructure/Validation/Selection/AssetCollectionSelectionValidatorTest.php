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

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\Selection;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection\AssetCollectionSelectionConstraint;
use Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Validation\AbstractValidationTest;
use Akeneo\Test\Integration\Configuration;

class AssetCollectionSelectionValidatorTest extends AbstractValidationTest
{
    /**
     * @dataProvider validSelection
     */
    public function test_it_does_not_build_violations_on_valid_selection(array $value): void
    {
        $violations = $this->getValidator()->validate($value, new AssetCollectionSelectionConstraint());

        $this->assertNoViolation($violations);
    }

    /**
     * @dataProvider invalidSelection
     */
    public function test_it_builds_violations_on_invalid_selection(
        string $expectedErrorMessage,
        string $expectedErrorPath,
        array $value
    ): void {
        $violations = $this->getValidator()->validate($value, new AssetCollectionSelectionConstraint());

        $this->assertHasValidationError($expectedErrorMessage, $expectedErrorPath, $violations);
    }

    public function validSelection(): array
    {
        return [
            'a code selection' => [
                [
                    'type' => 'code',
                    'separator' => ';',
                ],
            ],
            'a label selection' => [
                [
                    'type' => 'label',
                    'separator' => ',',
                    'locale' => 'en_US',
                ],
            ],
            'a media file selection' => [
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'property' => 'file_key'
                ]
            ],
            'a media link selection' => [
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => 'en_US',
                    'channel' => 'ecommerce',
                    'with_prefix_and_suffix' => true
                ]
            ]
        ];
    }

    public function invalidSelection(): array
    {
        return [
            'invalid type' => [
                'The value you selected is not a valid choice.',
                '[type]',
                [
                    'type' => 'invalid type',
                    'separator' => ',',
                ],
            ],
            'invalid separator' => [
                'The value you selected is not a valid choice.',
                '[separator]',
                [
                    'type' => 'code',
                    'separator' => 'foo',
                ],
            ],
            'blank locale' => [
                'This value should not be blank.',
                '[locale]',
                [
                    'type' => 'label',
                    'separator' => ';',
                    'locale' => '',
                ],
            ],
            'inactive locale' => [
                'akeneo.tailored_export.validation.locale.should_be_active',
                '[locale]',
                [
                    'type' => 'label',
                    'separator' => ';',
                    'locale' => 'fr_FR',
                ],
            ],
            'media file without property' => [
                'This field is missing.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                ]
            ],
            'media file with invalid property' => [
                'This value should be equal to "file_key".',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'property' => 'file_kéké'
                ]
            ],
            'media file without locale' => [
                'This field is missing.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'channel' => null,
                ]
            ],
            'media file with inactive locale' => [
                'akeneo.tailored_export.validation.locale.should_be_active',
                '[locale]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => 'fr_FR',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ]
            ],
            'media file without channel' => [
                'This field is missing.',
                '[property]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                ]
            ],
            'media file with unknown channel' => [
                'akeneo.tailored_export.validation.channel.should_exist',
                '[channel]',
                [
                    'type' => 'media_file',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => 'canal+',
                    'with_prefix_and_suffix' => true
                ]
            ],
            'media link without with_prefix_and_suffix' => [
                'This field is missing.',
                '[with_prefix_and_suffix]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                ]
            ],
            'media link with invalid with_prefix_and_suffix' => [
                'This value should be of type bool.',
                '[with_prefix_and_suffix]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => null,
                    'with_prefix_and_suffix' => 'trou'
                ]
            ],
            'media link without channel' => [
                'This field is missing.',
                '[channel]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'with_prefix_and_suffix' => true
                ]
            ],
            'media link with unknown channel' => [
                'akeneo.tailored_export.validation.channel.should_exist',
                '[channel]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => null,
                    'channel' => 'canal+',
                    'with_prefix_and_suffix' => true
                ]
            ],
            'media link without locale' => [
                'This field is missing.',
                '[locale]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ]
            ],
            'media link with inactive locale' => [
                'akeneo.tailored_export.validation.locale.should_be_active',
                '[locale]',
                [
                    'type' => 'media_link',
                    'separator' => '|',
                    'locale' => 'fr_FR',
                    'channel' => null,
                    'with_prefix_and_suffix' => true
                ]
            ],
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
