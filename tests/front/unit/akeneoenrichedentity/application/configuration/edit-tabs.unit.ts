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

  test('I can get the tab list', () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {
          first: {
            label: 'First tab',
            panel: 'view-to-load',
          },
        },
        default_tab: 'my-default-tab',
      },
      () => {}
    );

    expect(tabProvider.getTabs()).toEqual([{code: 'first', label: 'First tab'}]);
  });

  test('I can get a view', () => {
    const tabProvider = EditTabsProvider.create(
      {
        tabs: {
          first: {
            label: 'First tab',
            panel: 'view-to-load',
          },
        },
        default_tab: 'my-default-tab',
      },
      name => {
        expect(name).toEqual('view-to-load');

        return Promise.resolve({default: 'view'});
      }
    );

    tabProvider.getView('first').then(module => {
      expect(module.default).toEqual('view');
    });

    // expect(tabProvider.getView('first')).toEqual([{code: 'first', 'label': "First tab"}]);
  });
});
