import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {TextConfigurator} from './TextConfigurator';
import {getDefaultTextSource} from './model';
import {getDefaultDateSource} from '../Date/model';

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

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <TextConfigurator
        source={getDefaultDateSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for text configurator');

  mockedConsole.mockRestore();
});
