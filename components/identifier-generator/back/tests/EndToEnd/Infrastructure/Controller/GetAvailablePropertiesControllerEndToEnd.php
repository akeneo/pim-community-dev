<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAvailablePropertiesControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_get_properties', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_returns_http_forbidden_without_manage_generator_acl(): void
    {
        $this->loginAs('kevin');
        $this->callGetRouteWithQueryParam(
            'akeneo_identifier_generator_get_properties',
            ['systemFields' => ['free_text', 'auto_number', 'family']],
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_gets_a_list_of_available_properties(): void
    {
        $this->assertResponse(
            ['systemFields' => ['free_text', 'auto_number', 'family']],
            [
                [
                    'id' => 'system',
                    'text' => 'System',
                    'children' => [
                        [
                            'id' => 'free_text',
                            'text' => 'Free text',
                        ],
                        [
                            'id' => 'auto_number',
                            'text' => 'Auto Number',
                        ],
                        [
                            'id' => 'family',
                            'text' => 'Family',
                        ],
                    ],
                ],
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Attribute group B',
                    'children' => [
                        [
                            'id' => 'a_simple_select',
                            'text' => 'A simple select',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'The color',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'The size',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                    ],
                ],
            ]
        );
    }

    /** @test */
    public function it_gets_a_list_of_available_properties_with_ref_entities(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $this->createReferenceEntity('brand', []);
        $this->createRecords('brand', ['Akeneo', 'Other']);

        $this->createAttribute(
            [
                'code' => 'a_reference_entity_attribute',
                'type' => 'akeneo_reference_entity',
                'group' => 'other',
                'reference_data_name' => 'brand',
            ]
        );

        $this->assertResponse(
            ['systemFields' => ['free_text', 'auto_number', 'family']],
            [
                [
                    'id' => 'system',
                    'text' => 'System',
                    'children' => [
                        [
                            'id' => 'free_text',
                            'text' => 'Free text',
                        ],
                        [
                            'id' => 'auto_number',
                            'text' => 'Auto Number',
                        ],
                        [
                            'id' => 'family',
                            'text' => 'Family',
                        ],
                    ],
                ],
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Attribute group B',
                    'children' => [
                        [
                            'id' => 'a_simple_select',
                            'text' => 'A simple select',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'The color',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'The size',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                    ],
                ],
                [
                    'id' => 'other',
                    'text' => 'Other',
                    'children' => [
                        [
                            'id' => 'a_reference_entity_attribute',
                            'text' => '[a_reference_entity_attribute]',
                            'type' => 'akeneo_reference_entity',
                        ],
                    ],
                ],
            ]
        );
    }

    /** @test */
    public function it_gets_a_list_of_paginated_properties(): void
    {
        $this->assertResponse(
            ['systemFields' => ['free_text'], 'page' => 1, 'limit' => 3],
            [
                [
                    'id' => 'system',
                    'text' => 'System',
                    'children' => [
                        [
                            'id' => 'free_text',
                            'text' => 'Free text',
                        ],
                    ],
                ],
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Attribute group B',
                    'children' => [
                        [
                            'id' => 'a_simple_select',
                            'text' => 'A simple select',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'The color',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                    ],
                ],
            ]
        );
        $this->assertResponse(
            ['systemFields' => ['free_text'], 'page' => 2, 'limit' => 3],
            [
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Attribute group B',
                    'children' => [
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'The size',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                    ],
                ],
            ]
        );
    }

    public function it_can_search_through_available_properties(): void
    {
        $this->assertResponse(
            ['systemFields' => ['free_text', 'auto_number', 'family'], 'search' => 'The'],
            [
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Attribute group B',
                    'children' => [
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'The color',
                        ],
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'The size',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                    ],
                ],
            ]
        );
        $this->assertResponse(
            ['systemFields' => ['free_text', 'auto_number', 'family'], 'search' => 'Fam'],
            [
                [
                    'id' => 'system',
                    'text' => 'System',
                    'children' => [
                        [
                            'id' => 'family',
                            'text' => 'Family',
                        ],
                    ],
                ],
            ]
        );
    }

    /** @test */
    public function it_translates_the_results(): void
    {
        $this->assertResponse(
            ['systemFields' => ['free_text', 'auto_number', 'family'], 'dataLocale' => 'fr_FR'],
            [
                [
                    'id' => 'system',
                    'text' => 'Système',
                    'children' => [
                        [
                            'id' => 'free_text',
                            'text' => 'Texte libre',
                        ],
                        [
                            'id' => 'auto_number',
                            'text' => 'Nombre aléatoire',
                        ],
                        [
                            'id' => 'family',
                            'text' => 'Famille',
                        ],
                    ],
                ],
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Groupe d\'attribut B',
                    'children' => [
                        [
                            'id' => 'a_simple_select',
                            'text' => '[a_simple_select]',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'La couleur',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'La taille',
                            'type' => 'pim_catalog_simpleselect',
                        ],
                    ],
                ],
            ]
        );
    }

    /** @test */
    public function it_does_not_get_attributes_when_no_acl_granted(): void
    {
        $this->removeListAttributesPermissions();
        $this->loginAs('admin');

        $this->assertResponse(
            ['systemFields' => ['free_text', 'auto_number', 'family'], 'dataLocale' => 'fr_FR'],
            [
                [
                    'id' => 'system',
                    'text' => 'Système',
                    'children' => [
                        [
                            'id' => 'free_text',
                            'text' => 'Texte libre',
                        ],
                        [
                            'id' => 'auto_number',
                            'text' => 'Nombre aléatoire',
                        ],
                        [
                            'id' => 'family',
                            'text' => 'Famille',
                        ],
                    ],
                ],
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateAttributeLabels('a_simple_select', ['en_US' => 'A simple select']);
        $this->updateAttributeLabels('a_simple_select_color', ['en_US' => 'The color', 'fr_FR' => 'La couleur']);
        $this->updateAttributeLabels('a_simple_select_size', ['en_US' => 'The size', 'fr_FR' => 'La taille']);

        // update French system translations
        /** @var MessageCatalogueInterface $frenchCatalogue */
        $frenchCatalogue = $this->get('translator')->getCatalogue('fr_FR');
        $frenchCatalogue->set('pim_catalog_identifier_generator.structure.field_groups.system', 'Système');
        $frenchCatalogue->set('pim_catalog_identifier_generator.structure.fields.free_text', 'Texte libre');
        $frenchCatalogue->set('pim_catalog_identifier_generator.structure.fields.auto_number', 'Nombre aléatoire');
        $frenchCatalogue->set('pim_catalog_identifier_generator.structure.fields.family', 'Famille');

        $this->loginAs('Julia');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['identifier_generator']);
    }

    private function updateAttributeLabels(string $attributeCode, array $labels): void
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);
        $this->get('pim_catalog.updater.attribute')->update($attribute, ['labels' => $labels]);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function assertResponse(array $parameters, array $expectedResponse): void
    {
        $this->callRoute(
            'akeneo_identifier_generator_get_properties',
            self::DEFAULT_HEADER,
            $parameters
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertSame($expectedResponse, \json_decode($response->getContent(), true));
    }

    private function removeListAttributesPermissions(): void
    {
        $roleWithPermissions = $this->getAdminRoleWithPermissions();
        $permissions = $roleWithPermissions->permissions();

        $revokedPermissions = [];

        foreach ($permissions as $acl => $isGranted) {
            if ($acl === 'action:pim_enrich_attribute_index') {
                $revokedPermissions[$acl] = false;
            } else {
                $revokedPermissions[$acl] = true;
            }
        }

        $roleWithPermissions->setPermissions($revokedPermissions);

        $this->get('pim_user.saver.role_with_permissions')->saveAll([$roleWithPermissions]);

        $this->get('oro_security.acl.manager')->flush();
        $this->get('oro_security.acl.manager')->clearCache();
    }

    private function getAdminRoleWithPermissions(): ?RoleWithPermissions
    {
        return $this->get('pim_user.repository.role_with_permissions')->findOneByIdentifier('ROLE_ADMINISTRATOR');
    }
}
