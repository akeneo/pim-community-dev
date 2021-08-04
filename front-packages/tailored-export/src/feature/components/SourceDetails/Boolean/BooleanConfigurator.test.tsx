import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {BooleanReplacementOperation} from '../common/BooleanReplacement';
import {BooleanConfigurator} from './BooleanConfigurator';
import {getDefaultBooleanSource} from './model';
import {getDefaultDateSource} from '../Date/model';

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

test('it displays a boolean configurator', () => {
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

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  const dateAttribute = {
    code: 'date',
    type: 'pim_catalog_date',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  };

  expect(() => {
    renderWithProviders(
      <BooleanConfigurator
        attribute={attribute}
        source={getDefaultDateSource(dateAttribute, null, null)}
        validationErrors={[]}
        onSourceChange={onSourceChange}
      />
    );
  }).toThrow('Invalid source data "date" for boolean configurator');

  expect(screen.queryByText('Update replacement')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
