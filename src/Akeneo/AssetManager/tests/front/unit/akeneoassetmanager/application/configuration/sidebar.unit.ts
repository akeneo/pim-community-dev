import {TabsConfiguration, getTabs} from 'akeneoassetmanager/application/configuration/sidebar';
import {getView} from "../../../../../../front/application/configuration/sidebar";

describe('akeneo > asset family > application > configuration --- sidebar', () => {
  test('I can get the tab list', () => {

    const tabConfig: TabsConfiguration = {
      my_view: {
        tabs: {
          first: {
            label: 'First tab',
            view: require('akeneoassetmanager/application/component/asset-family/edit/attribute.tsx'),
          },
        },
      },
    };

    expect(getTabs(tabConfig, 'my_view')).toEqual([{code: 'first', label: 'First tab'}]);
  });

  test('I get a SidebarMissConfigurationError exception if the tabs are not well configured', () => {
    const tabConfig: TabsConfiguration = {
      // @ts-expect-error invalid tab configuration
      my_view: {}
    };

    try {
      getTabs(tabConfig, 'my_view');
    } catch (error) {
      const confPath = `
config:
  config:
    akeneoassetmanager/application/configuration/sidebar:
      my_view:
        tabs:
          tab-code:
            label: 'your.translation.key.here'

Actual conf: {\"my_view\":{}}`;
      expect(error.message).toBe(`Cannot get the tabs for "my_view". The configuration path should be ${confPath}`);
    }
  });

  test('I get a SidebarMissConfigurationError exception if the tabs are not well configured vith a dedicated label view', () => {
    const tabConfig: TabsConfiguration = {
      my_view: {
        tabs: {
          first: {
            // @ts-expect-error invalid tab configuration
            label: {},
          },
        },
      },
    };

    try {
      getTabs(tabConfig, 'my_view');
    } catch (error) {
      const confPath = `
config:
  config:
    akeneoassetmanager/application/configuration/sidebar:
      my_view:
        tabs:
          tab-code:
            label: 'your.translation.key.here'`;
      expect(error.message).toBe(`You need to define a label for your tab: ${confPath}`);
    }
  });

  test('I can get a view', () => {
    const view = require('akeneoassetmanager/application/component/asset-family/edit/attribute.tsx');
    const tabConfig: TabsConfiguration = {
      my_view: {
        tabs: {
          first: {
            label: 'First tab',
            view,
          },
        },
      },
    };

    expect(getView(tabConfig, 'my_view', 'first')).toEqual(view.default);
  });

  test('I can get a label view', () => {
    const tabConfig: TabsConfiguration = {
      my_view: {
        tabs: {
          first: {
            label: 'my_view',
            view: require('akeneoassetmanager/application/component/asset-family/edit/attribute.tsx')
          },
        },
      },
    };

    expect(getTabs(tabConfig, 'my_view')).toEqual([{code: 'first', label: 'my_view'}]);
  });

  test('I get a SidebarMissConfigurationError exception if the view is not well configured', () => {
    const tabConfig: TabsConfiguration = {
      my_view: {
        tabs: {},
      },
    };

    try {
      getView(tabConfig, 'my_view', 'first');
    } catch (error) {
      const confPath = `
config:
  config:
    akeneoassetmanager/application/configuration/sidebar:
      my_view:
        tabs:
          first:
            view: '@your_view_path_here'`;
      expect(error.message).toEqual(
        `Cannot load view for tab "first". The configuration should look like this ${confPath}

Actual conf: {\"my_view\":{\"tabs\":{}}}`
      );
    }
  });

  test('I get a SidebarMissConfigurationError exception if the view module is not well registered', () => {
    const tabConfig: TabsConfiguration = {
      my_view: {
        tabs: {
          first: {
            label: 'First tab',
            // @ts-expect-error invalid module
            view: 'view-to-load',
          },
        },
      },
    };

    try {
      getView(tabConfig, 'my_view', 'first');
    } catch (error) {
      expect(error.message).toBe(
        'The module "view-to-load" does not exists. You may have an error in your filter configuration file.'
      );
    }
  });
});
