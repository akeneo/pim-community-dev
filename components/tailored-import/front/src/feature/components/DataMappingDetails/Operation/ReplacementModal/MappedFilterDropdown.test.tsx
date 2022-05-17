import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from 'feature/tests';
import {MappedFilterDropdown} from './MappedFilterDropdown';

test('it displays a dropdown allowing to change the value to mapped', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(<MappedFilterDropdown value="unmapped" onChange={handleChange} />);

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label:')
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.mapped')
  );

  expect(handleChange).toHaveBeenCalledWith('mapped');
});

test('it displays a dropdown allowing to change the value to non mapped', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(<MappedFilterDropdown value="all" onChange={handleChange} />);

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label:')
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.unmapped')
  );

  expect(handleChange).toHaveBeenCalledWith('unmapped');
});

test('it displays a dropdown allowing to change the value to all values', async () => {
  const handleChange = jest.fn();

  await renderWithProviders(<MappedFilterDropdown value="unmapped" onChange={handleChange} />);

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.label:')
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_import.data_mapping.operations.replacement.modal.filters.mapped.all')
  );

  expect(handleChange).toHaveBeenCalledWith('all');
});
