import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ReferenceEntityConfigurator} from './ReferenceEntityConfigurator';
import {getDefaultReferenceEntitySource, ReferenceEntitySelection} from './model';
import {getDefaultDateSource} from '../Date/model';

const attribute = {
  code: 'ref_entity',
  type: 'akeneo_reference_entity',
  reference_data_name: 'brand',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/CodeLabelSelector');
jest.mock('../common/DefaultValue');
jest.mock('../common/RecordsReplacement');

jest.mock('./ReferenceEntitySelector', () => ({
  ReferenceEntitySelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: ReferenceEntitySelection) => void;
  }) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'attribute',
          attribute_identifier: 'description_1234',
          attribute_type: 'text',
          reference_entity_code: 'brand',
          channel: null,
          locale: null,
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a reference entity configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityConfigurator
      source={getDefaultReferenceEntitySource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntitySource(attribute, null, null),
    uuid: expect.any(String),
    selection: {
      type: 'attribute',
      attribute_identifier: 'description_1234',
      attribute_type: 'text',
      reference_entity_code: 'brand',
      channel: null,
      locale: null,
    },
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityConfigurator
      source={getDefaultReferenceEntitySource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntitySource(attribute, null, null),
    uuid: expect.any(String),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <ReferenceEntityConfigurator
        source={getDefaultDateSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for reference entity configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it can update a reference entity replacement operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityConfigurator
      source={getDefaultReferenceEntitySource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Records replacement'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntitySource(attribute, null, null),
    uuid: expect.any(String),
    operations: {
      replacement: {
        type: 'replacement',
        mapping: {
          foo: 'bar',
        },
      },
    },
  });
});

test('it throws when the attribute is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const invalidAttribute = {...attribute, reference_data_name: undefined};

  expect(() => {
    renderWithProviders(
      <ReferenceEntityConfigurator
        source={getDefaultReferenceEntitySource(attribute, null, null)}
        attribute={invalidAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Reference entity attribute "ref_entity" should have a reference_data_name');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
