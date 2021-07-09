import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {PropertySourceConfigurator} from './PropertySourceConfigurator';
import {Source} from '../../models';
import userEvent from '@testing-library/user-event';

jest.mock('./Enabled/EnabledConfigurator', () => ({
  ...jest.requireActual('./Enabled/EnabledConfigurator'),
  EnabledConfigurator: ({
    source,
    onSourceChange,
  }: {
    source: Source;
    onSourceChange: (updatedSource: Source) => void;
  }) => (
    <button
      onClick={() =>
        onSourceChange({
          ...source,
          locale: 'en_US',
        })
      }
    >
      Update source
    </button>
  ),
}));

test('it renders a property configurator', () => {
  const handleSourceChange = jest.fn();

  renderWithProviders(
    <PropertySourceConfigurator
      source={{
        code: 'enabled',
        uuid: 'unique_id',
        type: 'property',
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(screen.getByText('Update source')).toBeInTheDocument();
  userEvent.click(screen.getByText('Update source'));
  expect(handleSourceChange).toHaveBeenCalledWith({
    code: 'enabled',
    uuid: 'unique_id',
    type: 'property',
    locale: 'en_US',
  });
});

test('it render nothing if the configurator is unknown', () => {
  const handleSourceChange = jest.fn();
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  renderWithProviders(
    <PropertySourceConfigurator
      source={{
        code: 'nothing',
        uuid: 'unique_id',
        type: 'property',
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('No configurator found for "nothing" source code');
  mockedConsole.mockRestore();

  expect(screen.queryByText('Update source')).not.toBeInTheDocument();
});
