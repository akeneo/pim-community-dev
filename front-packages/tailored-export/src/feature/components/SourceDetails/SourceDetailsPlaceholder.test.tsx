import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SourceDetailsPlaceholder} from './SourceDetailsPlaceholder';

test('it renders source detail placeholder', () => {
  renderWithProviders(<SourceDetailsPlaceholder />);

  expect(
    screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_configuration.title/i)
  ).toBeInTheDocument();
});
