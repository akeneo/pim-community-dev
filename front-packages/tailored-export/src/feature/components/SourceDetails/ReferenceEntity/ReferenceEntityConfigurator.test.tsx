import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ReferenceEntityConfigurator} from './ReferenceEntityConfigurator';
import {getDefaultReferenceEntitySource} from './model';
import {getDefaultDateSource} from '../Date/model';
import {ReplacementOperation} from '../common';

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

jest.mock('./ReferenceEntityReplacement', () => ({
  ReferenceEntityReplacement: ({
    onOperationChange,
  }: {
    onOperationChange: (updatedOperation: ReplacementOperation) => void;
  }) => (
    <button
      onClick={() =>
        onOperationChange({
          type: 'replacement',
          mapping: {
            foo: 'bar',
          },
        })
      }
    >
      Reference entity replacement
    </button>
  ),
}));

test('it displays a reference entity configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityConfigurator
      source={{
        ...getDefaultReferenceEntitySource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntitySource(attribute, null, null),
    selection: {
      type: 'label',
      locale: 'en_US',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ReferenceEntityConfigurator
      source={{
        ...getDefaultReferenceEntitySource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntitySource(attribute, null, null),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
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
      source={{
        ...getDefaultReferenceEntitySource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Reference entity replacement'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultReferenceEntitySource(attribute, null, null),
    operations: {
      replacement: {
        type: 'replacement',
        mapping: {
          foo: 'bar',
        },
      },
    },
    selection: {
      type: 'code',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the attribute is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const invalidAttribute = {...attribute, reference_data_name: undefined};

  expect(() => {
    renderWithProviders(
      <ReferenceEntityConfigurator
        source={{
          ...getDefaultReferenceEntitySource(attribute, null, null),
          uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
        }}
        attribute={invalidAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Reference entity attribute "ref_entity" should have a reference_data_name');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
