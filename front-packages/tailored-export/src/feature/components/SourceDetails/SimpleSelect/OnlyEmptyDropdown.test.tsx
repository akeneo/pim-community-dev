import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {OnlyEmptyDropdown} from './OnlyEmptyDropdown';

test('it displays a dropdown allowing to change the value to only mapped', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(<OnlyEmptyDropdown value={false} onChange={handleChange} />);

  userEvent.click(
    screen.getByLabelText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.label:'
    )
  );

  userEvent.click(
    screen.getByText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.yes'
    )
  );

  expect(handleChange).toHaveBeenCalledWith(true);
});

test('it displays a dropdown allowing to change the value to all values', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(<OnlyEmptyDropdown value={true} onChange={handleChange} />);

  userEvent.click(
    screen.getByLabelText(
      'akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.label:'
    )
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.operation.replacement.modal.filters.only_mapped.no')
  );

  expect(handleChange).toHaveBeenCalledWith(false);
});
