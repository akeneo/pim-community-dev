import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {FamilyConfigurator} from './FamilyConfigurator';
import {CodeLabelSelection} from '../common/CodeLabelSelector';
import {getDefaultParentSource} from '../Parent/model';

jest.mock('../common/CodeLabelSelector', () => ({
  CodeLabelSelector: ({onSelectionChange}: {onSelectionChange: (updatedSelection: CodeLabelSelection) => void}) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'label',
          locale: 'en_US',
        })
      }
    >
      Update selection
    </button>
  ),
}));

jest.mock('../common/DefaultValue');

test('it displays a family configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <FamilyConfigurator
      source={{
        channel: null,
        code: 'family',
        locale: null,
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'property',
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    channel: null,
    code: 'family',
    locale: null,
    operations: {},
    selection: {
      locale: 'en_US',
      type: 'label',
    },
    type: 'property',
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <FamilyConfigurator
      source={{
        channel: null,
        code: 'family',
        locale: null,
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'property',
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    channel: null,
    code: 'family',
    locale: null,
    selection: {
      type: 'code',
    },
    type: 'property',
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
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

  expect(() => {
    renderWithProviders(
      <FamilyConfigurator source={getDefaultParentSource()} validationErrors={[]} onSourceChange={jest.fn()} />
    );
  }).toThrow('Invalid source data "parent" for family configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
