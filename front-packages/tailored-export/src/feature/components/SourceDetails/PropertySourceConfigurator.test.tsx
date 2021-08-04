import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {PropertySourceConfigurator} from './PropertySourceConfigurator';
import {Source} from '../../models';

test('it renders a property configurator', () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    code: 'enabled',
    uuid: 'unique_id',
    type: 'property',
    locale: null,
    channel: null,
    operations: {
      replacement: {type: 'replacement', mapping: {true: 'vra', false: 'faux'}},
    },
    selection: {
      type: 'code',
    },
  };

  renderWithProviders(
    <PropertySourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
  );

  userEvent.type(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.operation.replacement.enabled'),
    'i'
  );

  expect(handleSourceChange).toHaveBeenCalledWith({
    ...source,
    operations: {
      ...source.operations,
      replacement: {
        type: 'replacement',
        mapping: {true: 'vrai', false: 'faux'},
      },
    },
  });
});

test('it renders nothing if the configurator is unknown', () => {
  const handleSourceChange = jest.fn();
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <PropertySourceConfigurator
      // @ts-expect-error unknown source
      source={{
        code: 'nothing',
        uuid: 'unique_id',
        type: 'property',
        locale: null,
        channel: null,
        operations: {},
        selection: {
          type: 'code',
        },
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('No configurator found for "nothing" source code');
  mockedConsole.mockRestore();

  expect(screen.queryByText('Update source')).not.toBeInTheDocument();
});

test('it renders an invalid property placeholder when the source is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const handleSourceChange = jest.fn();

  renderWithProviders(
    <PropertySourceConfigurator
      // @ts-expect-error unknown selection type
      source={{
        code: 'enabled',
        uuid: 'unique_id',
        type: 'property',
        locale: null,
        channel: null,
        operations: {},
        selection: {type: 'path'},
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(screen.getByText('akeneo.tailored_export.column_details.sources.invalid_source.property')).toBeInTheDocument();
  mockedConsole.mockRestore();
});
