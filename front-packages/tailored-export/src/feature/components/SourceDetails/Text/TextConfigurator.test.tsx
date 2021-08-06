import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {TextConfigurator} from './TextConfigurator';
import {getDefaultTextSource} from './model';
import {getDefaultDateSource} from '../Date/model';
import {DefaultValueOperation} from '../common';
import userEvent from '@testing-library/user-event';

const attribute = {
  code: 'text',
  type: 'pim_catalog_text',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/DefaultValue', () => ({
  ...jest.requireActual('../common/DefaultValue'),
  DefaultValue: ({onOperationChange}: {onOperationChange: (updatedOperation: DefaultValueOperation) => void}) => (
    <button
      onClick={() =>
        onOperationChange({
          type: 'default_value',
          value: 'foo',
        })
      }
    >
      Default value
    </button>
  ),
}));

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <TextConfigurator
      attribute={attribute}
      source={{
        ...getDefaultTextSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultTextSource(attribute, null, null),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
    selection: {
      type: 'code',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

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
