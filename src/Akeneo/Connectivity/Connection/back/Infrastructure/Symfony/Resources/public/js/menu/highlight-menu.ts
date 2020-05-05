const mediator = require('oro/mediator');

const highlightConnectionsMenu = () => {
  // 'System' tab & column
  mediator.trigger('pim_menu:highlight:tab', {
    extension: 'pim-menu-system',
  });

  // 'Connections' item
  mediator.trigger('pim_menu:highlight:item', {
    extension: 'pim-menu-system-connection-settings',
  });
};

const highlightConnectionSettingsAndMonitoringMenu = (routeName: string) => {
  // 'System' tab & 'Connection' column
  mediator.trigger('pim_menu:highlight:tab', {
    extension: 'pim-menu-system',
    columnExtension: 'pim-menu-connection-column',
  });

  if (routeName === 'akeneo_connectivity_connection_error_management_connection_monitoring') {
    mediator.trigger('pim_menu:highlight:item', {
      extension: 'pim-menu-connection-monitoring',
    });
  } else {
    mediator.trigger('pim_menu:highlight:item', {
      extension: 'pim-menu-connection-settings-edit',
    });
  }
};

export default (routeName: string) => {
  if (routeName === 'akeneo_connectivity_connection_settings_index') {
    highlightConnectionsMenu();
  } else {
    highlightConnectionSettingsAndMonitoringMenu(routeName);
  }
};
