<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Validator;

use Akeneo\Pim\Permission\Component\Validator\UpdateUserGroupAttributeGroupsPermissions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUserGroupAttributeGroupsPermissionsValidatorIntegration extends KernelTestCase
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
        $violations = $this->validator->validate($payload, new UpdateUserGroupAttributeGroupsPermissions());
        $this->assertCount(0, $violations);
    }

    /**
     * @dataProvider invalidPayloadDataProvider
     */
    public function testItReturnsViolationsIfTheValueIsInvalid($payload): void
    {
        $violations = $this->validator->validate($payload, new UpdateUserGroupAttributeGroupsPermissions());
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
                        'edit' => [
                            'all' => null,
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
                        'edit' => [
                            'all' => false,
                            'identifiers' => '',
                        ],
                        'view' => [
                            'all' => false,
                            'identifiers' => [],
                        ],
                    ],
                ],
            ],
            'edit all = true && view all = false' =>  [
                [
                    'user_group' => 'Redactor',
                    'permissions' => [
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
