import React, {ReactNode} from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {UpdaterRow} from './UpdaterRow';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {view as TextInput} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/text';
import Channel from 'akeneoassetmanager/domain/model/channel';

const channels: Channel[] = [
  {
    "code": "ecommerce",
    "locales": [
      {
        "code": "en_US",
        "label": "English (United States)",
        "region": "United States",
        "language": "English"
      },
      {
        "code": "fr_FR",
        "label": "French (France)",
        "region": "France",
        "language": "French"
      }
    ],
    "labels": {
      "en_US": "Ecommerce",
      "de_DE": "Ecommerce",
      "fr_FR": "Ecommerce"
    },
  },
  {
    "code": "mobile",
    "locales": [
      {
        "code": "de_DE",
        "label": "German (Germany)",
        "region": "Germany",
        "language": "German"
      },
      {
        "code": "en_US",
        "label": "English (United States)",
        "region": "United States",
        "language": "English"
      },
    ],
    "labels": {
      "en_US": "Mobile",
      "de_DE": "Mobil",
      "fr_FR": "Mobile"
    }
  },
  {
    "code": "print",
    "locales": [
      {
        "code": "de_DE",
        "label": "German (Germany)",
        "region": "Germany",
        "language": "German"
      },
      {
        "code": "en_US",
        "label": "English (United States)",
        "region": "United States",
        "language": "English"
      },
      {
        "code": "fr_FR",
        "label": "French (France)",
        "region": "France",
        "language": "French"
      }
    ],
    "labels": {
      "en_US": "Print",
      "de_DE": "Drucken",
      "fr_FR": "Impression"
    },
  }
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
  action: 'set',
};

const renderUpdaterRow = (row: ReactNode) => {
  renderWithProviders(
    <ConfigProvider
      config={{
        value: {
          text: {
            view: {view: TextInput},
          },
        },
      }}
    >
      <table>
        <tbody>
          {row}
        </tbody>
      </table>
    </ConfigProvider>
  );
}

test('it renders its children properly', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={() => {}}
      onRemove={() => {}}
    />
  );

  expect(screen.getByText('Description attribute')).toBeInTheDocument();
  expect(screen.getByText('English')).toBeInTheDocument();
  expect(screen.getByText('Ecommerce')).toBeInTheDocument();
  expect(screen.getByTitle('pim_common.remove')).toBeInTheDocument();
});

test('it calls onChange handler when user change input', () => {
  const handleChange = jest.fn();
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={() => {}}
    />
  );

  const textInput = screen.getByLabelText('Description attribute');
  fireEvent.change(textInput, { target: { value: 'New value' } })
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, data: 'New value'});
});

test('it calls onChange handler when user change the channel', () => {
  const handleChange = jest.fn();
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={() => {}}
    />
  );

  const channelSelector = screen.getByText('Ecommerce');
  fireEvent.click(channelSelector);
  const newChannelOption = screen.getByTitle('Mobile');
  fireEvent.click(newChannelOption);
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, channel: 'mobile'});
});

test('it calls onChange handler when user change the locale', () => {
  const handleChange = jest.fn();
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={() => {}}
    />
  );

  const localeSelector = screen.getByText('English');
  fireEvent.click(localeSelector);
  const newLocaleOption = screen.getByLabelText('FR');
  fireEvent.click(newLocaleOption);
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, locale: 'fr_FR'});
});

test('it call onRemove handler when user remove line', () => {
  const handleRemove = jest.fn();
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={() => {}}
      onRemove={handleRemove}
    />
  );

  const removeButton = screen.getByTitle('pim_common.remove');
  fireEvent.click(removeButton);
  expect(handleRemove).toHaveBeenCalledTimes(1);
  expect(handleRemove).toHaveBeenCalledWith(updater);
});

test('it does not permit user action on readonly', () => {
  const handleChange = jest.fn();
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={true}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={() => {}}
    />
  );

  expect(screen.getByText('Ecommerce')).toHaveAttribute('aria-disabled', 'true');
  expect(screen.getByText('English').closest("button")).toHaveAttribute('aria-disabled', 'true');

  const textInput = screen.getByLabelText('Description attribute');
  fireEvent.change(textInput, { target: { value: 'New value' } })
  expect(handleChange).not.toHaveBeenCalled();

  expect(screen.queryByTitle('pim_common.remove')).not.toBeInTheDocument();
});

test('it display validation errors', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={updater}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[
        {
          messageTemplate: 'This value should not be blank.',
          parameters: {'{{ value }}': '""'},
          message: 'This value should not be blank.',
          propertyPath: 'description',
          invalidValue: '',
        },
      ]}
      onChange={() => {}}
      onRemove={() => {}}
    />
  );

  expect(screen.getByText('description: This value should not be blank.')).toBeInTheDocument();
});

test('it does not display the channel and locale dropdown when attribute is not scopable and not localisable', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={{...updater, locale: null, channel: null}}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={() => {}}
      onRemove={() => {}}
    />
  );

  expect(screen.queryByText('English')).not.toBeInTheDocument();
  expect(screen.queryByText('Ecommerce')).not.toBeInTheDocument();
});

test('it display all locales when attribute is not scopable', () => {
  renderUpdaterRow(
    <UpdaterRow
      updater={{...updater, channel: null}}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={() => {}}
      onRemove={() => {}}
    />
  );

  const localeSelector = screen.getByText('English');
  fireEvent.click(localeSelector);

  expect(screen.getByLabelText('DE')).toBeInTheDocument();
  expect(screen.getByLabelText('FR')).toBeInTheDocument();
  expect(screen.getAllByLabelText('US').length).toEqual(2);
});

test('it change the locale if selected channel does not contain the current locale', () => {
  const handleChange = jest.fn();
  renderUpdaterRow(
    <UpdaterRow
      updater={{...updater, locale: 'fr_FR'}}
      readOnly={false}
      uiLocale="en_US"
      channels={channels}
      errors={[]}
      onChange={handleChange}
      onRemove={() => {}}
    />
  );

  const channelSelector = screen.getByText('Ecommerce');
  fireEvent.click(channelSelector);
  const newChannelOption = screen.getByTitle('Mobile');
  fireEvent.click(newChannelOption);
  expect(handleChange).toHaveBeenCalledTimes(1);
  expect(handleChange).toHaveBeenCalledWith({...updater, channel: 'mobile', locale: 'de_DE'});
});

