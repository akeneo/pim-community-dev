<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAvailableConditionsControllerEndToEnd extends ControllerEndToEndTestCase
{
    /** @test */
    public function it_should_redirect_on_non_xhr_request(): void
    {
        $this->loginAs('Julia');
        $this->callRoute('akeneo_identifier_generator_get_conditions', [
            'HTTP_X-Requested-With' => 'toto',
        ]);
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /** @test */
    public function it_returns_http_forbidden_without_the_manage_generator_acl(): void
    {
        $this->loginAs('mary');
        $this->callRoute('akeneo_identifier_generator_get_conditions');
        $response = $this->client->getResponse();

        Assert::AssertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /** @test */
    public function it_gets_a_list_of_available_conditions(): void
    {
        $this->assertResponse(
            ['systemFields' => ['family', 'enabled']],
            [
                [
                    'id' => 'system',
                    'text' => 'System',
                    'children' => [
                        [
                            'id' => 'family',
                            'text' => 'Family',
                        ],
                        [
                            'id' => 'enabled',
                            'text' => 'Status',
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
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'The color',
                        ],
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'The size',
                        ],
                    ],
                ],
            ]
        );
    }

    /** @test */
    public function it_gets_a_list_of_paginated_conditions(): void
    {
        $this->assertResponse(
            ['systemFields' => ['enabled'], 'page' => 1, 'limit' => 3],
            [
                [
                    'id' => 'system',
                    'text' => 'System',
                    'children' => [
                        [
                            'id' => 'enabled',
                            'text' => 'Status',
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
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'The color',
                        ],
                    ],
                ],
            ]
        );
        $this->assertResponse(
            ['systemFields' => ['enabled'], 'page' => 2, 'limit' => 3],
            [
                [
                    'id' => 'attributeGroupB',
                    'text' => 'Attribute group B',
                    'children' => [
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'The size',
                        ],
                    ],
                ],
            ]
        );
    }

    public function it_can_search_through_available_conditions(): void
    {
        $this->assertResponse(
            ['systemFields' => ['family', 'enabled'], 'search' => 'The'],
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
                        ],
                    ],
                ],
            ]
        );
        $this->assertResponse(
            ['systemFields' => ['family', 'enabled'], 'search' => 'Fam'],
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
            ['systemFields' => ['family', 'enabled'], 'dataLocale' => 'fr_FR'],
            [
                [
                    'id' => 'system',
                    'text' => 'Système',
                    'children' => [
                        [
                            'id' => 'family',
                            'text' => 'Famille',
                        ],
                        [
                            'id' => 'enabled',
                            'text' => 'Statut',
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
                        ],
                        [
                            'id' => 'a_simple_select_color',
                            'text' => 'La couleur',
                        ],
                        [
                            'id' => 'a_simple_select_size',
                            'text' => 'La taille',
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
        $frenchCatalogue->set('pim_identifier_generator.condition.field_groups.system', 'Système');
        $frenchCatalogue->set('pim_identifier_generator.condition.fields.family', 'Famille');
        $frenchCatalogue->set('pim_identifier_generator.condition.fields.enabled', 'Statut');

        $this->loginAs('Julia');
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
            'akeneo_identifier_generator_get_conditions',
            self::DEFAULT_HEADER,
            $parameters
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertSame($expectedResponse, \json_decode($response->getContent(), true));
    }
}
