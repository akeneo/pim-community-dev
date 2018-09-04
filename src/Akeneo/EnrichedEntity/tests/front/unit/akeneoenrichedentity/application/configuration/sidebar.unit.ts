import {TabsProvider} from 'akeneoenrichedentity/application/configuration/sidebar';

jest.mock('require-context', name => {});

describe('akeneo > enriched entity > application > configuration --- sidebar', () => {
  test('I can get the tab list', () => {
    const tabProvider = TabsProvider.create(
      {
        my_view: {
          tabs: {
            first: {
              label: 'First tab',
              view: 'view-to-load',
            },
          },
        },
      },
      () => {}
    );

    expect(tabProvider.getTabs('my_view')).toEqual([{code: 'first', label: 'First tab'}]);
  });

  test('I get a SibebarMissConfigurationError exception if the tabs are not well configured', () => {
    const tabProvider = TabsProvider.create(
      {
        my_view: {},
      },
      () => {}
    );

    try {
      tabProvider.getTabs('my_view');
    } catch (error) {
      const confPath = `
config:
    config:
        akeneoenrichedentity/application/configuration/sidebar:
            my_view:
                tabs:
                    tab-code:
                        view: your_view_path_here
      `;
      expect(error.message).toBe(`Cannot get the tabs configured. The configuration path should be ${confPath}?`);
    }
  });

  test('I can get a view', async () => {
    const tabProvider = TabsProvider.create(
      {
        my_view: {
          tabs: {
            first: {
              label: 'First tab',
              view: 'view-to-load',
            },
          },
        },
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve({default: 'view'});
      }
    );

    await tabProvider.getView('my_view', 'first').then(module => {
      expect(module).toEqual('view');
    });
    expect.assertions(2);
  });

  test('I get a SibebarMissConfigurationError exception if the view is not well configured', async () => {
    const tabProvider = TabsProvider.create(
      {
        my_view: {
          tabs: {},
        },
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve({default: 'view'});
      }
    );

    try {
      await tabProvider.getView('my_view', 'first');
    } catch (error) {
      const confPath = `
config:
    config:
        akeneoenrichedentity/application/configuration/sidebar:
            my_view:
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
    const tabProvider = TabsProvider.create(
      {
        my_view: {
          tabs: {
            first: {
              label: 'First tab',
              view: 'view-to-load',
            },
          },
        },
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve(undefined);
      }
    );

    try {
      await tabProvider.getView('my_view', 'first');
    } catch (error) {
      expect(error.message).toBe(
        'The module "view-to-load" does not exists. You may have an error in your filter configuration file.'
      );
    }
  });
});
