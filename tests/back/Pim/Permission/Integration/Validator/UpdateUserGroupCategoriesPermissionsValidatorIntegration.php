<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Validator;

use Akeneo\Pim\Permission\Component\Validator\UpdateUserGroupCategoriesPermissions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUserGroupCategoriesPermissionsValidatorIntegration extends KernelTestCase
{
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['debug' => false]);
        $this->validator = self::getContainer()->get('validator');
    }

    public function testItReturnsNoViolationsIfTheValueIsValid(): void
    {
        $payload = [
            'user_group' => 'Redactor',
            'permissions' => [
                'own' => [
                    'all' => false,
                    'identifiers' => [],
                ],
                'edit' => [
                    'all' => false,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => false,
                    'identifiers' => [],
                ],
            ],
        ];
        $violations = $this->validator->validate($payload, new UpdateUserGroupCategoriesPermissions());
        $this->assertCount(0, $violations);
    }

    /**
     * @dataProvider invalidPayloadDataProvider
     */
    public function testItReturnsViolationsIfTheValueIsInvalid($payload): void
    {
        $violations = $this->validator->validate($payload, new UpdateUserGroupCategoriesPermissions());
        $this->assertGreaterThan(0, $violations->count());
    }

    public function invalidPayloadDataProvider(): array
    {
        return [
            'not an array' => [
                null,
            ],
            'user group not a string' => [
                [
                    'user_group' => null,
                ],
            ],
            'permissions not an array' => [
                [
                    'user_group' => 'Redactor',
                    'permissions' => null,
                ],
            ],
            'permissions levels are missing' => [
                [
                    'user_group' => 'Redactor',
                    'permissions' => [],
                ],
            ],
            'option "all" is not a boolean' => [
                [
                    'user_group' => 'Redactor',
                    'permissions' => [
                        'own' => [
                            'all' => null,
                            'identifiers' => [],
                        ],
                        'edit' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                        'view' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                    ],
                ],
            ],
            '"identifiers" is not an array' => [
                [
                    'user_group' => 'Redactor',
                    'permissions' => [
                        'own' => [
                            'all' => false,
                            'identifiers' => null,
                        ],
                        'edit' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                        'view' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                    ],
                ],
            ],
            'own all = true && edit all = false' => [
                [
                    'user_group' => 'Redactor',
                    'permissions' => [
                        'own' => [
                            'all' => true,
                            'identifiers' => [],
                        ],
                        'edit' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                        'view' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                    ],
                ],
            ],
            'edit all = true && view all = false' => [
                [
                    'user_group' => 'Redactor',
                    'permissions' => [
                        'own' => [
                            'all' => true,
                            'identifiers' => [],
                        ],
                        'edit' => [
                            'all' => true,
                            'identifiers' => [],
                        ],
                        'view' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                    ],
                ],
            ],
        ];
    }
}
