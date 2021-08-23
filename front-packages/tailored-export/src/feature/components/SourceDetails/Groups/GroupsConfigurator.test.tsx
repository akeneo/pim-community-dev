import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {GroupsConfigurator} from './GroupsConfigurator';
import {getDefaultGroupsSource} from './model';
import {getDefaultParentSource} from '../Parent/model';

jest.mock('../common/CodeLabelCollectionSelector');
jest.mock('../common/DefaultValue');

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <GroupsConfigurator
      source={{
        channel: null,
        code: 'groups',
        locale: null,
        operations: {},
        selection: {
          type: 'code',
          separator: ',',
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
    code: 'groups',
    locale: null,
    selection: {
      type: 'code',
      separator: ',',
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

test('it tells when the source data is invalid', () => {
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
