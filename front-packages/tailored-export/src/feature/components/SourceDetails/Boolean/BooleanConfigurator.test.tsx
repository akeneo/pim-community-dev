import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
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
/**
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
*/
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

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
  /**
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
  */
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
