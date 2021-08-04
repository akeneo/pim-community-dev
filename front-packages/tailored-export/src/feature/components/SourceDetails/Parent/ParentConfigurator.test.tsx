import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ParentConfigurator} from './ParentConfigurator';
import {getDefaultParentSource} from '../../../components/SourceDetails/Parent/model';
import {getDefaultGroupsSource} from '../Groups/model';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

jest.mock('./ParentSelector', () => ({
  ParentSelector: ({
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

test('it displays a parent configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <ParentConfigurator
      source={{
        ...getDefaultParentSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultParentSource(),
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
      <ParentConfigurator source={getDefaultGroupsSource()} validationErrors={[]} onSourceChange={onSourceChange} />
    );
  }).toThrow('Invalid source data "groups" for parent configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
