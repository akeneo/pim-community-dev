import {EditTabsProvider} from 'akeneoenrichedentity/application/configuration/edit-tabs';

jest.mock('require-context', name => {});

describe('akeneo > enriched entity > application > configuration --- edit-tabs', () => {
  test('I can get the default tab', () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {},
        default_tab: 'my-default-tab',
      },
      () => {}
    );

    expect(tabProvider.getDefaultTab()).toEqual('my-default-tab');
  });

  test('I get a SibebarMissConfigurationError exception if the default tab is not well configured', () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {},
      },
      () => {}
    );

    try {
      tabProvider.getDefaultTab();
    } catch (error) {
      const confPath = `
config:
    config:
       akeneoenrichedentity/application/configuration/edit-tabs:
            default_tab: tab-code
      `;
      expect(error.message).toBe(`Cannot get the default tab. The configuration path should be ${confPath}?`);
    }
  });

  test('I can get the tab list', () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {
          first: {
            label: 'First tab',
            view: 'view-to-load',
          },
        },
        default_tab: 'my-default-tab',
      },
      () => {}
    );

    expect(tabProvider.getTabs()).toEqual([{code: 'first', label: 'First tab'}]);
  });

  test('I get a SibebarMissConfigurationError exception if the tabs are not well configured', () => {
    const tabProvider = EditTabsProvider.create(
      {
        default_tab: 'my-default-tab',
      },
      () => {}
    );

    try {
      tabProvider.getTabs();
    } catch (error) {
      const confPath = `
config:
    config:
       akeneoenrichedentity/application/configuration/edit-tabs:
            tabs:
                tab-code:
                    view: your_view_path_here
      `;
      expect(error.message).toBe(`Cannot get the tabs configured. The configuration path should be ${confPath}?`);
    }
  });

  test('I can get a view', async () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {
          first: {
            label: 'First tab',
            view: 'view-to-load',
          },
        },
        default_tab: 'my-default-tab',
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve({default: 'view'});
      }
    );

    await tabProvider.getView('first').then(module => {
      expect(module).toEqual('view');
    });
    expect.assertions(2);
  });

  test('I get a SibebarMissConfigurationError exception if the view is not well configured', async () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {},
        default_tab: 'my-default-tab',
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve({default: 'view'});
      }
    );

    try {
      await tabProvider.getView('first');
    } catch (error) {
      const confPath = `
config:
    config:
       akeneoenrichedentity/application/configuration/edit-tabs:
            tabs:
                first:
                    view: your_view_path_here
      `;
      expect(error.message).toBe(
        `Cannot load view configuration for tab "first". The configuration path should be ${confPath}?`
      );
    }
  });

  test('I get a SibebarMissConfigurationError exception if the view module is not well registered', async () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {
          first: {
            label: 'First tab',
            view: 'view-to-load',
          },
        },
        default_tab: 'my-default-tab',
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve(undefined);
      }
    );

    try {
      await tabProvider.getView('first');
    } catch (error) {
      expect(error.message).toBe(
        'The module "view-to-load" does not exists. You may have an error in your filter configuration file.'
      );
    }
  });
});
