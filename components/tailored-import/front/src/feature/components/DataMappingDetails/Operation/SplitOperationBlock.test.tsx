import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {getDefaultSplitOperation, SplitOperationBlock} from './SplitOperationBlock';

test('it can get the default split operation', () => {
  expect(getDefaultSplitOperation()).toEqual({
    type: 'split',
    separator: ',',
  });
});

test('it displays a split operation block', () => {
  renderWithProviders(
    <SplitOperationBlock operation={{type: 'split', separator: ','}} onChange={jest.fn()} onRemove={jest.fn()} />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.split.title')).toBeInTheDocument();
});

test('it can be removed using the remove button', () => {
  const handleRemove = jest.fn();

  renderWithProviders(
    <SplitOperationBlock operation={{type: 'split', separator: ','}} onChange={jest.fn()} onRemove={handleRemove} />
  );

  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.remove')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_common.delete'));

  expect(handleRemove).toHaveBeenCalledWith('split');
});

test('it can change the separator', () => {
  const handleChange = jest.fn();

  renderWithProviders(
    <SplitOperationBlock operation={{type: 'split', separator: ','}} onChange={handleChange} onRemove={jest.fn()} />
  );

  userEvent.click(screen.getByTitle('Collapse'));
  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByTitle('semicolon'));

  expect(handleChange).toHaveBeenCalledWith({type: 'split', separator: ';'});
});

test('it throws an error if the operation is not a split operation', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <SplitOperationBlock operation={{type: 'clean_html_tags'}} onChange={jest.fn()} onRemove={jest.fn()} />
    );
  }).toThrowError('SplitOperationBlock can only be used with SplitOperation');

  mockedConsole.mockRestore();
});
