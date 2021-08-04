import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {GroupsConfigurator} from './GroupsConfigurator';
import {getDefaultGroupsSource} from '../../../components/SourceDetails/Groups/model';
import {getDefaultParentSource} from '../Parent/model';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

jest.mock('../common/CodeLabelCollectionSelector', () => ({
  CodeLabelCollectionSelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
  }) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'label',
          locale: 'en_US',
          separator: ',',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a groups configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <GroupsConfigurator
      source={{
        ...getDefaultGroupsSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultGroupsSource(),
    selection: {
      locale: 'en_US',
      separator: ',',
      type: 'label',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  expect(() => {
    renderWithProviders(
      <GroupsConfigurator source={getDefaultParentSource()} validationErrors={[]} onSourceChange={onSourceChange} />
    );
  }).toThrow('Invalid source data "parent" for groups configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
