import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DataMappingListPlaceholder} from './DataMappingListPlaceholder';

test('it renders a placeholder when no data mapping is selected', () => {
  renderWithProviders(<DataMappingListPlaceholder onDataMappingCreated={jest.fn()} />);

  expect(screen.getByText(/akeneo.syndication.data_mapping_list.no_data_mapping_selection.title/i)).toBeInTheDocument();
});

test('it calls the add data mapping handler when clicking on the button', () => {
  const handleDataMappingCreated = jest.fn();

  renderWithProviders(<DataMappingListPlaceholder onDataMappingCreated={handleDataMappingCreated} />);

  fireEvent.click(screen.getByText('akeneo.syndication.data_mapping_list.no_data_mapping_selection.add_data_mapping'));

  expect(handleDataMappingCreated).toBeCalled();
});
