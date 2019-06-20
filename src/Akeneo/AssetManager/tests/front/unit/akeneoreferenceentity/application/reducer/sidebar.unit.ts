import reducer from 'akeneoreferenceentity/application/reducer/sidebar';
import {Tab} from 'akeneoreferenceentity/application/reducer/sidebar';

describe('akeneo > reference entity > application > reducer --- sidebar', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({});
  });

  test('I can toggle the sidebar', () => {
    const state = {};
    const collapsedState = reducer(state, {
      type: 'TOGGLE_SIDEBAR',
      isCollapsed: true,
    });

    expect(collapsedState).toEqual({
      isCollapsed: true,
    });

    const uncollapsedState = reducer(state, {
      type: 'TOGGLE_SIDEBAR',
      isCollapsed: false,
    });

    expect(uncollapsedState).toEqual({
      isCollapsed: false,
    });
  });

  test('I can set up tabs', () => {
    const state = {};
    const tabs: Tab[] = [
      {
        code: 'tab-1',
        label: 'Tab 1',
      },
      {
        code: 'tab-2',
        label: 'Tab 2',
      },
    ];
    const newState = reducer(state, {
      type: 'SETUP_SIDEBAR_TABS',
      tabs,
    });

    expect(newState).toEqual({
      tabs,
    });
  });

  test('I can update current tab', () => {
    const state = {};
    const currentTab: string = 'tab-1';
    const newState = reducer(state, {
      type: 'UPDATE_CURRENT_SIDEBAR_TAB',
      currentTab,
    });

    expect(newState).toEqual({
      currentTab,
    });
  });
});
