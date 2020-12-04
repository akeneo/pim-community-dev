import React from 'react';
import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useInitialLocalesIndexState, useLocalesIndexState} from '@akeneo-pim-community/settings-ui/src/hooks/locales';
import {fetchActivatedLocales} from '@akeneo-pim-community/settings-ui/src/infrastructure/fetchers/localesFetcher';
import {aListOfLocales} from '../../../utils/provideLocaleHelper';
import {act} from 'react-test-renderer';

jest.mock('@akeneo-pim-community/settings-ui/src/infrastructure/fetchers/localesFetcher');

describe('useInitialLocalesDataGridState', () => {
  const renderUseInitialLocalesIndexState = () => {
    return renderHookWithProviders(useInitialLocalesIndexState);
  };
  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.resetAllMocks();
  });

  test('it initializes the state for Locales datagrid', () => {
    const {result} = renderUseInitialLocalesIndexState();

    expect(result.current.locales).toEqual([]);
    expect(result.current.load).toBeDefined();
    expect(result.current.isPending).toBeTruthy();
  });

  test('it loads the activated locales', async () => {
    const activatedLocales = aListOfLocales(['en_US', 'fr_FR', 'en_US']);

    // @ts-ignore
    fetchActivatedLocales.mockResolvedValue(activatedLocales);

    const {result} = renderUseInitialLocalesIndexState();

    expect(result.current.isPending).toBeTruthy();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.isPending).toBeFalsy();

    expect(result.current.locales).toBe(activatedLocales);
  });
});

describe('useLocalesIndexState', () => {
  const renderUseLocalesIndexState = () => {
    return renderHookWithProviders(useLocalesIndexState);
  };
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it throws an error if it used outside Locales datagrid context', () => {
    jest.spyOn(React, 'useContext').mockImplementation(() => undefined);

    const {result} = renderUseLocalesIndexState();

    expect(result.error).not.toBeNull();
  });

  test('it returns context', () => {
    const {result} = renderUseLocalesIndexState();

    expect(result.current.locales).toEqual([]);
    expect(result.current.load).toBeDefined();
    expect(result.current.isPending).toBeTruthy();
  });
});
