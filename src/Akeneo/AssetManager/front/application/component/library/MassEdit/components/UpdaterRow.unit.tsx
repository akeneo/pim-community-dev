import React, {ReactNode} from 'react';
import {screen, fireEvent, within} from '@testing-library/react';
import {UpdaterRow} from './UpdaterRow';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {view as TextInput} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/text';
import Channel from 'akeneoassetmanager/domain/model/channel';

const channels: Channel[] = [
  {
    code: 'ecommerce',
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        region: 'France',
        language: 'French',
      },
    ],
    labels: {
      en_US: 'Ecommerce',
      de_DE: 'Ecommerce',
      fr_FR: 'Ecommerce',
    },
  },
  {
    code: 'mobile',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
        region: 'Germany',
        language: 'German',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
    ],
    labels: {
      en_US: 'Mobile',
      de_DE: 'Mobil',
      fr_FR: 'Mobile',
    },
  },
  {
    code: 'print',
    locales: [
      {
        code: 'de_DE',
        label: 'German (Germany)',
        region: 'Germany',
        language: 'German',
      },
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        region: 'France',
        language: 'French',
      },
    ],
    labels: {
      en_US: 'Print',
      de_DE: 'Drucken',
      fr_FR: 'Impression',
    },
  },
];

const updater = {
  id: 'uuid_random',
  channel: 'ecommerce',
  locale: 'en_US',
  attribute: {
    identifier: 'description',
    labels: {
      en_US: 'Description attribute',
    },
    type: 'text',
    code: 'description',
  },
  data: 'the value',
  action: 'replace',
};
const defaultValueView = {
  view: {view: TextInput},
};

const renderUpdaterRow = (row: ReactNode) => {
  renderWithProviders(
    <ConfigProvider
      config={{
        value: {
          text: defaultValueView,
          option_collection: defaultValueView,
        },
      }}
    >
      <table>
        <tbody>{row}</tbody>
      </table>
    </ConfigProvider>
  );
};

test('it renders its children properly', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={jest.fn()}
      onRemove={jest.fn()}
    />
  );

  expect(screen.getByText('Description attribute')).toBeInTheDocument();
  expect(screen.getByText('English')).toBeInTheDocument();
  expect(screen.getByText('Ecommerce')).toBeInTheDocument();
  expect(screen.getByTitle('pim_common.remove')).toBeInTheDocument();
});

test('it calls onChange handler when the user changes input', () => {
  const handleChange = jest.fn();

  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  const textInput = screen.getByLabelText('Description attribute');
  fireEvent.change(textInput, {target: {value: 'New value'}});
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, data: 'New value'});
});

test('it calls onChange handler when the user changes the channel', () => {
  const handleChange = jest.fn();

  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  const channelSelect = screen.getByTitle('pim_asset_manager.asset.mass_edit.select.channel');
  fireEvent.click(within(channelSelect).getByRole('textbox'));
  fireEvent.click(screen.getByText('Mobile'));
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, channel: 'mobile'});
});

test('it calls onChange handler when the user changes the locale', () => {
  const handleChange = jest.fn();

  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  const localeSelect = screen.getByTitle('pim_asset_manager.asset.mass_edit.select.locale');
  fireEvent.click(within(localeSelect).getByRole('textbox'));
  fireEvent.click(screen.getByText('French'));
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, locale: 'fr_FR'});
});

test('it calls onChange handler when the user changes the action on an option collection attribute', () => {
  const handleChange = jest.fn();
  const optionCollectionUpdater = {
    ...updater,
    attribute: {
      identifier: 'tags',
      labels: {
        en_US: 'Tags attribute',
      },
      type: 'option_collection',
      code: 'tags',
    },
  };

  renderUpdaterRow(
    <UpdaterRow
      updater={optionCollectionUpdater}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  const localeSelect = screen.getByTitle('pim_asset_manager.asset.mass_edit.select.action');
  fireEvent.click(within(localeSelect).getByRole('textbox'));
  fireEvent.click(screen.getByText('pim_asset_manager.asset.mass_edit.action.append'));
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...optionCollectionUpdater, action: 'append'});
});

test('it calls onRemove handler when the user remove line', () => {
  const handleRemove = jest.fn();

  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={jest.fn()}
      onRemove={handleRemove}
    />
  );

  const removeButton = screen.getByTitle('pim_common.remove');
  fireEvent.click(removeButton);
  expect(handleRemove).toHaveBeenCalledTimes(1);
  expect(handleRemove).toHaveBeenCalledWith(updater);
});

test('it does not permit user action when readonly', () => {
  const handleChange = jest.fn();

  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={true}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  screen.getAllByRole('textbox').forEach(input => expect(input).toHaveAttribute('readonly'));

  const textInput = screen.getByLabelText('Description attribute');
  fireEvent.change(textInput, {target: {value: 'New value'}});
  expect(handleChange).not.toHaveBeenCalled();

  expect(screen.queryByTitle('pim_common.remove')).not.toBeInTheDocument();
});

test('it displays validation errors', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      uiLocale="en_US"
      channels={channels}
      errors={[
        {
          messageTemplate: 'This value should not be blank.',
          parameters: {'{{ value }}': '""'},
          message: 'This value should not be blank.',
          propertyPath: 'updaters.uuid_random',
          invalidValue: '',
        },
      ]}
      onChange={jest.fn()}
      onRemove={jest.fn()}
    />
  );

  expect(screen.getByText('This value should not be blank.')).toBeInTheDocument();
});

test('it does not display the channel and locale dropdown when attribute is not scopable and not localisable', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={{...updater, locale: null, channel: null}}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={jest.fn()}
      onRemove={jest.fn()}
    />
  );

  expect(screen.queryByTitle('pim_asset_manager.asset.mass_edit.select.channel')).not.toBeInTheDocument();
  expect(screen.queryByTitle('pim_asset_manager.asset.mass_edit.select.locale')).not.toBeInTheDocument();
});

test('it displays all locales when attribute is not scopable', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={{...updater, channel: null}}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={jest.fn()}
      onRemove={jest.fn()}
    />
  );

  const localeSelect = screen.getByTitle('pim_asset_manager.asset.mass_edit.select.locale');
  fireEvent.click(within(localeSelect).getByRole('textbox'));

  expect(screen.getByText('German')).toBeInTheDocument();
  expect(screen.getByText('French')).toBeInTheDocument();
  expect(screen.getAllByText('English')).toHaveLength(2);
});

test('it changes the locale if selected channel does not contain the current locale', () => {
  const handleChange = jest.fn();

  renderUpdaterRow(
    <UpdaterRow
      updater={{...updater, locale: 'fr_FR'}}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={jest.fn()}
    />
  );

  const channelSelect = screen.getByTitle('pim_asset_manager.asset.mass_edit.select.channel');
  fireEvent.click(within(channelSelect).getByRole('textbox'));
  fireEvent.click(screen.getByText('Mobile'));

  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, channel: 'mobile', locale: 'de_DE'});
});
