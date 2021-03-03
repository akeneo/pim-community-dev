import {TabsProvider} from 'akeneoassetmanager/application/configuration/sidebar';

jest.mock('require-context', name => {});
jest.mock('akeneoassetmanager/tools/security-context', name => {});

describe('akeneo > asset family > application > configuration --- sidebar', () => {
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
        akeneoassetmanager/application/configuration/sidebar:
            my_view:
                tabs:
                    tab-code:
                        label: 'your.translation.key.here'

Actual conf: {\"my_view\":{}}`;
      expect(error.message).toBe(`Cannot get the tabs for "my_view". The configuration path should be ${confPath}`);
    }
  });

  test('I get a SibebarMissConfigurationError exception if the tabs are not well configured vith a dedicated label view', () => {
    const tabProvider = TabsProvider.create(
      {
        my_view: {
          tabs: {
            first: {
              label: {},
            },
          },
        },
      },
      () => {}
    );

    try {
      tabProvider.getTabs('my_view');
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
    const tabProvider = TabsProvider.create({
      my_view: {
        tabs: {
          first: {
            label: 'First tab',
            view: {default: 'view'},
          },
        },
      },
    });

    expect(tabProvider.getView('my_view', 'first')).toEqual('view');
  });

  test('I can get a label view', () => {
    const tabProvider = TabsProvider.create({
      my_view: {
        tabs: {
          first: {
            label: {
              label: 'my_view',
            },
          },
        },
      },
    });

    expect(tabProvider.getTabs('my_view', 'first')).toEqual([{code: 'first', label: 'my_view'}]);
  });

  test('I get a SibebarMissConfigurationError exception if the view is not well configured', () => {
    const tabProvider = TabsProvider.create({
      my_view: {
        tabs: {},
      },
    });

    try {
      tabProvider.getView('my_view', 'first');
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

  test('I get a SibebarMissConfigurationError exception if the view module is not well registered', () => {
    const tabProvider = TabsProvider.create({
      my_view: {
        tabs: {
          first: {
            label: 'First tab',
            view: 'view-to-load',
          },
        },
      },
    });

    try {
      tabProvider.getView('my_view', 'first');
    } catch (error) {
      expect(error.message).toBe(
        'The module "view-to-load" does not exists. You may have an error in your filter configuration file.'
      );
    }
  });
});
