import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {EnabledConfigurator} from './EnabledConfigurator';
import {getDefaultEnabledSource} from './model';
import {BooleanReplacementOperation} from '../common/BooleanReplacement';
import {getDefaultParentSource} from '../Parent/model';

jest.mock('../common/BooleanReplacement', () => ({
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

test('it displays an enabled configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <EnabledConfigurator
      source={{
        ...getDefaultEnabledSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update replacement'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultEnabledSource(),
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

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() => {
    renderWithProviders(
      <EnabledConfigurator source={getDefaultParentSource()} validationErrors={[]} onSourceChange={jest.fn()} />
    );
  }).toThrow('Invalid source data "parent" for enabled configurator');

  expect(screen.queryByText('Update replacement')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
