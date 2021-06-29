import React from 'react';
import {fireEvent} from '@testing-library/react';
import {renderWithProviders} from '../tests/utils';
import {SearchBar} from './SearchBar';

test('It calls the onSearchChange callback when the value is changed', () => {
  const onSearchChange = jest.fn();

  const {getByTitle} = renderWithProviders(<SearchBar onSearchChange={onSearchChange} searchValue="hey" count={12} />);

  fireEvent.change(getByTitle('pim_common.search'), {target: {value: 'hey!'}});

  expect(onSearchChange).toBeCalledWith('hey!');
});
