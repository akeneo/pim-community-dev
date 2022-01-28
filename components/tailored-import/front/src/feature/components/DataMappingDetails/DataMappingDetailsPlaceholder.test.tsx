import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DataMappingDetailsPlaceholder} from './DataMappingDetailsPlaceholder';

test('it renders a placeholder when no data mapping is selected', () => {
  renderWithProviders(<DataMappingDetailsPlaceholder />);

  expect(screen.getByText('akeneo.tailored_import.data_mapping.title')).toBeInTheDocument();
  expect(screen.getByText('akeneo.tailored_import.data_mapping.no_data_mapping_selected')).toBeInTheDocument();
});
