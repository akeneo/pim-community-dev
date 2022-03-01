<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use PhpSpec\ObjectBehavior;

class ScopeMapperRegistrySpec extends ObjectBehavior
{
    public function let(
        ScopeMapperInterface $productScopes,
        ScopeMapperInterface $catalogStructureScopes
    ): void {
        $productScopes->getScopes()->willReturn(['read_products', 'write_products']);
        $catalogStructureScopes->getScopes()->willReturn(['read_catalog_structure', 'write_catalog_structure']);

        $this->beConstructedWith([$productScopes, $catalogStructureScopes]);
    }

    public function it_is_a_scope_mapper_registry(): void
    {
        $this->shouldHaveType(ScopeMapperRegistry::class);
    }

    public function it_accepts_only_scope_mapper_interface(): void
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    \sprintf(
                        '%s must implement %s',
                        ScopeMapperRegistry::class,
                        ScopeMapperInterface::class
                    )
                )
            )
            ->during('__construct', [[new \stdClass()]]);
    }

    public function it_forbids_to_support_a_scope_more_than_once(): void
    {
        $anyScopeMapper = new class implements ScopeMapperInterface {
            public function getScopes(): array
            {
                return ['read_something'];
            }

            public function getAcls(string $scopeName): array
            {
                return [];
            }

            public function getMessage(string $scopeName): array
            {
                return [];
            }

            public function getLowerHierarchyScopes(string $scopeName): array
            {
                return [];
            }
        };
        $anotherScopeMapper = new class implements ScopeMapperInterface {
            public function getScopes(): array
            {
                return ['read_something'];
            }

            public function getAcls(string $scopeName): array
            {
                return [];
            }

            public function getMessage(string $scopeName): array
            {
                return [];
            }

            public function getLowerHierarchyScopes(string $scopeName): array
            {
                return [];
            }
        };

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__construct', [[$anyScopeMapper, $anotherScopeMapper]]);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getAllScopes()->shouldReturn([
            'read_products',
            'write_products',
            'read_catalog_structure',
            'write_catalog_structure',
        ]);
    }

    public function it_provides_filtered_messages_by_removing_lower_hierarchy_scopes(
        ScopeMapperInterface $productScopes,
        ScopeMapperInterface $catalogStructureScopes
    ): void {
        $productScopes->getLowerHierarchyScopes('read_products')->willReturn([]);
        $productScopes->getLowerHierarchyScopes('write_products')->willReturn(['read_products']);
        $catalogStructureScopes->getLowerHierarchyScopes('read_catalog_structure')->willReturn([]);
        $catalogStructureScopes->getLowerHierarchyScopes('write_catalog_structure')->willReturn(['read_catalog_structure']);

        $productScopes->getMessage('write_products')->willReturn([
            'icon' => 'write_products_icon',
            'type' => 'write',
            'entities' => 'products',
        ]);
        $catalogStructureScopes->getMessage('write_catalog_structure')->willReturn([
            'icon' => 'write_catalog_structure_icon',
            'type' => 'write',
            'entities' => 'catalog_structure',
        ]);

        $this
            ->getMessages(['write_products', 'write_catalog_structure', 'read_products', 'read_catalog_structure'])
            ->shouldReturn([
                [
                    'icon' => 'write_catalog_structure_icon',
                    'type' => 'write',
                    'entities' => 'catalog_structure',
                ],
                [
                    'icon' => 'write_products_icon',
                    'type' => 'write',
                    'entities' => 'products',
                ]
            ]);
    }

    public function it_provides_complete_acls_by_adding_lower_hierarchy_acls_if_missing(
        ScopeMapperInterface $productScopes,
        ScopeMapperInterface $catalogStructureScopes
    ): void {
        $productScopes->getLowerHierarchyScopes('read_products')->willReturn([]);
        $productScopes->getLowerHierarchyScopes('write_products')->willReturn(['read_products']);
        $catalogStructureScopes->getLowerHierarchyScopes('write_catalog_structure')->willReturn(['read_catalog_structure']);

        $productScopes->getAcls('read_products')->willReturn(['pim_api_product_list']);
        $productScopes->getAcls('write_products')->willReturn(['pim_api_product_edit']);

        $catalogStructureScopes->getAcls('read_catalog_structure')->willReturn([
            'pim_api_attribute_list',
            'pim_api_attribute_group_list',
            'pim_api_family_list',
            'pim_api_family_variant_list',
        ]);
        $catalogStructureScopes->getAcls('write_catalog_structure')->willReturn([
            'pim_api_attribute_edit',
            'pim_api_attribute_group_edit',
            'pim_api_family_edit',
            'pim_api_family_variant_edit',
        ]);

        $this
            ->getAcls(['write_products', 'write_catalog_structure', 'read_products'])
            ->shouldReturn([
                'pim_api_product_list',
                'pim_api_attribute_edit',
                'pim_api_attribute_group_edit',
                'pim_api_family_edit',
                'pim_api_family_variant_edit',
                'pim_api_product_edit',
                'pim_api_attribute_list',
                'pim_api_attribute_group_list',
                'pim_api_family_list',
                'pim_api_family_variant_list',
            ]);
    }

    public function it_leads_to_an_error_to_ask_for_acl_of_an_unknown_scope(): void
    {
        $this
            ->shouldThrow(new \LogicException('The scope "product_unknown_scope" does not exist.'))
            ->during('getAcls', [['product_unknown_scope']]);
    }

    public function it_leads_to_an_error_to_ask_for_message_of_an_unknown_scope(): void
    {
        $this
            ->shouldThrow(new \LogicException('The scope "product_unknown_scope" does not exist.'))
            ->during('getMessages', [['product_unknown_scope']]);
    }
}
