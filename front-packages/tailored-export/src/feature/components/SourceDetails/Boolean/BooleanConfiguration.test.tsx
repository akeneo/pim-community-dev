import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {BooleanConfigurator} from './BooleanConfigurator';
import {getDefaultBooleanSource} from './model';

const attribute = {
  code: 'boolean',
  type: 'pim_catalog_boolean',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

test('it displays a boolean configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <BooleanConfigurator
      source={getDefaultBooleanSource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
});
