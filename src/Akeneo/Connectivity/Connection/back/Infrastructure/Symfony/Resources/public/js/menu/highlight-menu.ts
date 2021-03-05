const mediator = require('oro/mediator');

const highlightSystemNavigationConnectionsMenuItem = () => {
  // 'System' tab & column
  mediator.trigger('pim_menu:highlight:tab', {
    extension: 'pim-menu-system',
  });

  // 'Connections' item
  mediator.trigger('pim_menu:highlight:item', {
    extension: 'pim-menu-system-connection-settings',
  });
};

const highlightConnectionNavigationMenuItems = (routeName: string) => {
  // 'System' tab & 'Connection' column
  mediator.trigger('pim_menu:highlight:tab', {
    extension: 'pim-menu-system',
    columnExtension: 'pim-menu-connection-column',
  });

  switch(routeName) {
    case 'akeneo_connectivity_connection_error_management_connection_monitoring':
      mediator.trigger('pim_menu:highlight:item', {
        extension: 'pim-menu-connection-monitoring',
      });
      break;
    case 'akeneo_connectivity_connection_settings_edit':
      mediator.trigger('pim_menu:highlight:item', {
        extension: 'pim-menu-connection-settings-edit',
      });
      break;
    case 'akeneo_connectivity_connection_webhook_edit':
      mediator.trigger('pim_menu:highlight:item', {
        extension: 'pim-menu-connection-event-subscriptions',
      });
      break;
    case 'akeneo_connectivity_connection_webhook_event_logs':
      mediator.trigger('pim_menu:highlight:item', {
        extension: 'pim-menu-connection-event-logs',
      });
      break;
  }
};

export default (routeName: string) => {
  if (routeName === 'akeneo_connectivity_connection_settings_index') {
    highlightSystemNavigationConnectionsMenuItem();

    return;
  }

  highlightConnectionNavigationMenuItems(routeName);
};
