import React from 'react';
import SearchField from 'akeneoassetmanager/application/component/asset/list/search-bar/search-field';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

// We need to use fake timers because SearchField uses a debounce function
jest.useFakeTimers();

test('It displays an empty search field', () => {
  renderWithProviders(<SearchField value="" onChange={jest.fn()} />);

  const input = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search') as HTMLInputElement;

  expect(screen.getByPlaceholderText('pim_asset_manager.asset.grid.search')).toBeInTheDocument();
  expect(input.value).toEqual('');
});

test('It displays a search value for the field', () => {
  const searchCriteria = 'SOME SEARCH';

  renderWithProviders(<SearchField value={searchCriteria} onChange={jest.fn()} />);

  const input = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search') as HTMLInputElement;

  expect(input.value).toEqual(searchCriteria);
});

test('It calls the onChange callback when it is updated', async () => {
  const onChange = jest.fn();
  let value = '';

  renderWithProviders(<SearchField value={value} onChange={onChange} />);

  const newValue = 'SOME NEW SEARCH CRITERIA';
  const input = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search') as HTMLInputElement;

  await act(async () => {
    await userEvent.type(input, newValue);
    jest.runAllTimers();
  });

  expect(onChange).toBeCalledWith(newValue);
});
