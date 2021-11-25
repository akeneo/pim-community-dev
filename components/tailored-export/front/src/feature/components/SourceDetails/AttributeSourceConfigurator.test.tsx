import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {AttributeSourceConfigurator} from './AttributeSourceConfigurator';
import {Source} from '../../models';
import {renderWithProviders} from 'feature/tests';

test('it displays source configurator', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: null,
    channel: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn()} />
  );

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.operation.header/i)).toBeInTheDocument();
});

test('it displays locale dropdown when attribute is localizable', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn()} />
  );

  expect(screen.getByLabelText(/pim_common.locale/i)).toBeInTheDocument();
  expect(screen.queryByLabelText(/pim_common.channel/i)).not.toBeInTheDocument();
});

test('it displays a filtered locale dropdown when attribute is localizable and locale specific', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'locale_specific',
    type: 'attribute',
    locale: 'de_DE',
    channel: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} onSourceChange={jest.fn()} validationErrors={[]} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.locale/i));

  expect(screen.getAllByTitle('German (Germany)').length).toEqual(2);
  expect(screen.queryByTitle('English (United States)')).not.toBeInTheDocument();
});

test('it displays a channel dropdown when attribute is scopable', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: null,
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn()} />
  );

  expect(screen.queryByLabelText(/pim_common.locale/i)).not.toBeInTheDocument();
  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
});

test('it displays a channel dropdown when attribute is scopable and localizable', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn()} />
  );

  expect(screen.getByLabelText(/pim_common.locale/i)).toBeInTheDocument();
  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
});

test('it calls handler when channel is changed', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.channel/i));
  userEvent.click(screen.getByText('[mobile]'));

  expect(handleSourceChange).toHaveBeenCalledWith({...source, locale: 'de_DE', channel: 'mobile'});
});

test('it calls handler when locale is changed', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.locale/i));
  userEvent.click(screen.getByText('English (United States)'));

  expect(handleSourceChange).toHaveBeenCalledWith({...source, locale: 'en_US'});
});

test('it displays attribute errors when attribute is not found', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'invalid_attribute',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator
      source={source}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '',
          invalidValue: '',
        },
      ]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(screen.getByText('code error message')).toBeInTheDocument();
});

test('it displays attribute errors when attribute is found', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator
      source={source}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '',
          invalidValue: '',
        },
      ]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(screen.getByText('code error message')).toBeInTheDocument();
});

test('it renders nothing if the configurator is unknown', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const handleSourceChange = jest.fn();

  await renderWithProviders(
    <AttributeSourceConfigurator
      source={{
        code: 'nothing',
        uuid: 'unique_id',
        type: 'attribute',
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

  expect(mockedConsole).toHaveBeenCalledWith('No configurator found for "pim_catalog_nothing" attribute type');
  mockedConsole.mockRestore();
});

test('it renders an invalid attribute placeholder when the source is invalid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const handleSourceChange = jest.fn();

  await renderWithProviders(
    <AttributeSourceConfigurator
      source={{
        code: 'weight',
        uuid: 'unique_id',
        type: 'attribute',
        locale: null,
        channel: null,
        operations: {},
        // @ts-expect-error invalid selection
        selection: {},
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.invalid_source.attribute')
  ).toBeInTheDocument();
  mockedConsole.mockRestore();
});
