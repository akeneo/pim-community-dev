import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DataMappingDetailsPlaceholder, NoSourcePlaceholder} from './DataMappingDetailsPlaceholder';

test('it renders a placeholder when no source is selected', () => {
  renderWithProviders(<NoSourcePlaceholder />);

  expect(
    screen.getByText(/akeneo.syndication.data_mapping_details.sources.no_source_selected.title/i)
  ).toBeInTheDocument();
});

test('it renders a placeholder when no dataMapping is selected', () => {
  renderWithProviders(<DataMappingDetailsPlaceholder />);

  expect(
    screen.getByText(/akeneo.syndication.data_mapping_details.sources.no_data_mapping_selected.title/i)
  ).toBeInTheDocument();
});
