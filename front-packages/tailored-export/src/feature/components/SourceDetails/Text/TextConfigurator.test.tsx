import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {TextConfigurator} from './TextConfigurator';
import {getDefaultTextSource} from './model';

const attribute = {
  code: 'text',
  type: 'pim_catalog_text',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

test('it displays a text configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <TextConfigurator
      source={getDefaultTextSource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
});
