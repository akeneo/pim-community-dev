import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ReferenceEntityCollectionConfigurator} from './ReferenceEntityCollectionConfigurator';
import {getDefaultReferenceEntityCollectionSource} from './model';
import {getDefaultDateSource} from '../Date/model';

const attribute = {
  code: 'reference_entity_collection',
  type: 'akeneo_reference_entity_collection',
  reference_data_name: 'brand',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/CodeLabelCollectionSelector');
jest.mock('../common/DefaultValue');
jest.mock('../common/RecordsReplacement');

test('it displays a reference entity collection configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityCollectionConfigurator
      source={{
        ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      requirement={{
        code: 'test',
        label: 'label',
        help: 'help',
        group: 'general',
        required: false,
        examples: [],
        type: 'string',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
    selection: {
      type: 'label',
      locale: 'en_US',
      separator: ',',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityCollectionConfigurator
      source={{
        ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      requirement={{
        code: 'test',
        label: 'label',
        help: 'help',
        group: 'general',
        required: false,
        examples: [],
        type: 'string',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update a reference entity collection replacement operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityCollectionConfigurator
      source={{
        ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      requirement={{
        code: 'test',
        label: 'label',
        help: 'help',
        group: 'general',
        required: false,
        examples: [],
        type: 'string',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Records replacement'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
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

test('it throws when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <ReferenceEntityCollectionConfigurator
        source={getDefaultDateSource(dateAttribute, null, null)}
        requirement={{
          code: 'test',
          label: 'label',
          help: 'help',
          group: 'general',
          required: false,
          examples: [],
          type: 'string',
        }}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for reference entity collection configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});

test('it throws when the attribute is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const invalidAttribute = {...attribute, reference_data_name: undefined};

  expect(() => {
    renderWithProviders(
      <ReferenceEntityCollectionConfigurator
        source={{
          ...getDefaultReferenceEntityCollectionSource(attribute, null, null),
          uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
        }}
        requirement={{
          code: 'test',
          label: 'label',
          help: 'help',
          group: 'general',
          required: false,
          examples: [],
          type: 'string',
        }}
        attribute={invalidAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Reference entity collection attribute "reference_entity_collection" should have a reference_data_name');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
