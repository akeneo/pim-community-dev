import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CodeConfigurator} from './CodeConfigurator';
import {getDefaultCodeSource} from './model';
import {getDefaultParentSource} from '../Parent/model';

jest.mock('../common/BooleanReplacement');

test('it displays a code configurator', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'string',
    label: 'String',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  renderWithProviders(
    <CodeConfigurator
      requirement={requirement}
      source={{...getDefaultCodeSource(), uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640'}}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.no_source_configuration.title')
  ).toBeInTheDocument();
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const requirement = {
    code: 'string',
    label: 'String',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  expect(() => {
    renderWithProviders(
      <CodeConfigurator
        requirement={requirement}
        source={getDefaultParentSource()}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "parent" for code configurator');

  mockedConsole.mockRestore();
});
