import React from 'react';
import {UpdaterRow} from './UpdaterRow';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {ConfigProvider} from 'akeneoassetmanager/application/hooks/useConfig';
import {view as TextInput} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/text';

test('it renders its children properly', () => {
  global.fetch = jest.fn().mockImplementation(() => new Promise(() => {}));

  const updater = {
    id: 'uuid_random',
    channel: null,
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

  const {getByText} = renderWithProviders(
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
          <UpdaterRow
            updater={updater}
            readOnly={false}
            uiLocale="en_US"
            channels={[]}
            errors={[]}
            onChange={() => {}}
            onRemove={() => {}}
          />
        </tbody>
      </table>
    </ConfigProvider>
  );

  expect(getByText('Description attribute')).toBeInTheDocument();
  expect(getByText(`en_USset`)).toBeInTheDocument();
});
