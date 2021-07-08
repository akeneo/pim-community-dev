import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {NumberConfigurator} from './NumberConfigurator';
import {getDefaultNumberSource} from './model';

const attribute = {
  code: 'number',
  type: 'pim_catalog_number',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

test('it displays a number configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <NumberConfigurator
      source={getDefaultNumberSource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
});
