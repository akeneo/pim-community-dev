<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Component\Security;

use Akeneo\Channel\Component\Security\ChannelScopeMapper;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use PhpSpec\ObjectBehavior;

class ChannelScopeMapperSpec extends ObjectBehavior
{
    public function it_is_a_channel_scope_mapper(): void
    {
        $this->shouldHaveType(ChannelScopeMapper::class);
        $this->shouldImplement(ScopeMapperInterface::class);
    }

    public function it_provides_all_scopes(): void
    {
        $this->getScopes()->shouldReturn([
            'read_channel_localization',
            'read_channel_settings',
            'write_channel_settings',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_channel_localization_scope(): void
    {
        $this->getAcls('read_channel_localization')->shouldReturn([
            'pim_api_locale_list',
            'pim_api_currency_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_read_channel_settings_scope(): void
    {
        $this->getAcls('read_channel_settings')->shouldReturn([
            'pim_api_channel_list',
        ]);
    }

    public function it_provides_acls_that_correspond_to_the_write_channel_settings_scope(): void
    {
        $this->getAcls('write_channel_settings')->shouldReturn([
            'pim_api_channel_edit',
        ]);
    }

    public function it_does_not_provide_acl_if_an_unknown_scope_is_given(): void
    {
        $this->getAcls('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_message_that_corresponds_to_the_read_channel_localization_scope(): void
    {
        $this->getMessage('read_channel_localization')->shouldReturn([
            'icon' => 'channel_localization',
            'type' => 'view',
            'entities' => 'channel_localization',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_read_channel_settings_scope(): void
    {
        $this->getMessage('read_channel_settings')->shouldReturn([
            'icon' => 'channel_settings',
            'type' => 'view',
            'entities' => 'channel_settings',
        ]);
    }

    public function it_provides_message_that_corresponds_to_the_write_channel_settings_scope(): void
    {
        $this->getMessage('write_channel_settings')->shouldReturn([
            'icon' => 'channel_settings',
            'type' => 'edit',
            'entities' => 'channel_settings',
        ]);
    }

    public function it_does_not_provide_message_if_an_unknown_scope_is_given(): void
    {
        $this->getMessage('unknown_scope')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_channel_localization_scope(): void
    {
        $this->getLowerHierarchyScopes('read_channel_localization')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_read_channel_settings_scope(): void
    {
        $this->getLowerHierarchyScopes('read_channel_settings')->shouldReturn([]);
    }

    public function it_provides_lower_hierarchy_scopes_of_the_write_channel_settings_scope(): void
    {
        $this->getLowerHierarchyScopes('write_channel_settings')->shouldReturn([
            'read_channel_settings',
        ]);
    }

    public function it_does_not_provide_lower_hierarchy_scopes_for_an_unknown_scope(): void
    {
        $this->getLowerHierarchyScopes('unknown_scope')->shouldReturn([]);
    }
}
