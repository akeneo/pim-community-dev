import React from 'react';
import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useLocalesIndexState} from '@akeneo-pim-community/settings-ui/src/hooks/locales';

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
