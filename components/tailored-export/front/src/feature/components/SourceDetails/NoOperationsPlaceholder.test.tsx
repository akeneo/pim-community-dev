import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {NoOperationsPlaceholder} from './NoOperationsPlaceholder';

test('it renders no operations placeholder', () => {
  renderWithProviders(<NoOperationsPlaceholder />);

  expect(
    screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_configuration.title/i)
  ).toBeInTheDocument();
});
