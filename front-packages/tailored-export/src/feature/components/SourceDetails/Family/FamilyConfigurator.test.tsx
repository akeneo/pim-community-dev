import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {FamilyConfigurator} from './FamilyConfigurator';
import {getDefaultTextSource} from '../Text/model';
import {CodeLabelSelection} from '../common/CodeLabelSelector';

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

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  const textAttribute = {
    code: 'text',
    type: 'pim_catalog_text',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  };

  renderWithProviders(
    <FamilyConfigurator
      source={getDefaultTextSource(textAttribute, null, null)}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "text" for family configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
