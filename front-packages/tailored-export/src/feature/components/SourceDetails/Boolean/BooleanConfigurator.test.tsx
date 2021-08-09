import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {BooleanConfigurator} from './BooleanConfigurator';
import {getDefaultBooleanSource} from './model';
import {getDefaultDateSource} from '../Date/model';
import {BooleanReplacementOperation} from "../common";

const attribute = {
  code: 'boolean',
  type: 'pim_catalog_boolean',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/BooleanReplacement', () => ({
  ...jest.requireActual('../common/BooleanReplacement'),
  BooleanReplacement: ({
    onOperationChange,
  }: {
    onOperationChange: (updatedOperation: BooleanReplacementOperation) => void;
  }) => (
    <button
      onClick={() =>
        onOperationChange({
          type: 'replacement',
          mapping: {
            true: 'activé',
            false: 'désactivé',
          },
        })
      }
    >
      Update replacement
    </button>
  ),
}));

jest.mock('../common/DefaultValue');

test('it can update replacement operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <BooleanConfigurator
      attribute={attribute}
      source={{
        ...getDefaultBooleanSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update replacement'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultBooleanSource(attribute, null, null),
    operations: {
      replacement: {
        type: 'replacement',
        mapping: {
          true: 'activé',
          false: 'désactivé',
        },
      },
    },
    selection: {
      type: 'code',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <BooleanConfigurator
      attribute={attribute}
      source={{
        ...getDefaultBooleanSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultBooleanSource(attribute, null, null),
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
      <BooleanConfigurator
        attribute={dateAttribute}
        source={getDefaultDateSource(dateAttribute, null, null)}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for boolean configurator');

  expect(screen.queryByText('Update replacement')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
