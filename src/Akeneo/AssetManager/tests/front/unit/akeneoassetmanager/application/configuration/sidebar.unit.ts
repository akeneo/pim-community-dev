import {TabsConfiguration, getTabs} from 'akeneoassetmanager/application/configuration/sidebar';
import {getView} from 'akeneoassetmanager/application/configuration/sidebar';
import {fakeConfig} from '../../utils/FakeConfigProvider';
import {default as AttributeEditView} from 'akeneoassetmanager/application/component/asset-family/edit/attribute';

const fakeSecurity = {isGranted: () => true};

describe('akeneo > asset family > application > configuration --- sidebar', () => {
  test('I can get the tab list', () => {
    expect(getTabs(fakeSecurity, fakeConfig.sidebar, 'akeneo_asset_manager_asset_family_edit')).toEqual([
      {
        code: 'attribute',
        label: 'First tab',
      },
    ]);
  });

  test('I get a SidebarMissConfigurationError exception if the tabs are not well configured', () => {
    const tabConfig: TabsConfiguration = {
      // @ts-expect-error invalid tab configuration
      my_view: {},
    };

    try {
      getTabs(fakeSecurity, tabConfig, 'my_view');
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
      getTabs(fakeSecurity, tabConfig, 'my_view');
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
    expect(getView(fakeConfig.sidebar, 'akeneo_asset_manager_asset_family_edit', 'attribute')).toEqual(
      AttributeEditView
    );
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
