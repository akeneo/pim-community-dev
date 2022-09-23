import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {IdentifierConfigurator} from './IdentifierConfigurator';
import {getDefaultIdentifierSource} from './model';
import {getDefaultDateSource} from '../Date/model';

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
  const requirement = {
    code: 'identifier',
    label: 'Identifier',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  renderWithProviders(
    <IdentifierConfigurator
      requirement={requirement}
      source={getDefaultIdentifierSource(attribute)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.operation.extract.label')
  ).toBeInTheDocument();
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};
  const requirement = {
    code: 'identifier',
    label: 'Identifier',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  expect(() => {
    renderWithProviders(
      <IdentifierConfigurator
        requirement={requirement}
        source={getDefaultDateSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for identifier configurator');

  mockedConsole.mockRestore();
});
