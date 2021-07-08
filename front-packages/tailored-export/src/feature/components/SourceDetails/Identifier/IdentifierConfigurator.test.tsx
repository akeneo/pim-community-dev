import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {IdentifierConfigurator} from './IdentifierConfigurator';
import {getDefaultIdentifierSource} from './model';

const attribute = {
  code: 'identifier',
  type: 'pim_catalog_identifier',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

test('it displays a identifier configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <IdentifierConfigurator
      source={getDefaultIdentifierSource(attribute)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
});
