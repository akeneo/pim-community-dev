'use strict';

import {createFakeChannel, createFakeLocale} from '../tools';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {
  getOptionsFromChannels,
  getOptionsFromLocales,
  formatLocaleOption,
} from 'akeneoassetmanager/application/asset-upload/utils/select2';

describe('akeneoassetmanager/application/asset-upload/utils/select2.ts -> getOptionsFromChannels', () => {
  test('I can create translated options for select2 from a list of channels', () => {
    const channels: Channel[] = [
      {
        ...createFakeChannel('ecommerce', ['en_US']),
        labels: {
          en_US: 'ecommerce-en_US',
          fr_FR: 'ecommerce-fr_FR',
        },
      },
      {
        ...createFakeChannel('print', ['en_US']),
        labels: {
          en_US: 'print-en_US',
          fr_FR: 'print-fr_FR',
        },
      },
    ];

    expect(getOptionsFromChannels(channels, 'en_US')).toEqual({
      ecommerce: 'ecommerce-en_US',
      print: 'print-en_US',
    });
    expect(getOptionsFromChannels(channels, 'fr_FR')).toEqual({
      ecommerce: 'ecommerce-fr_FR',
      print: 'print-fr_FR',
    });
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/select2.ts -> getOptionsFromLocales', () => {
  test('I can create options for select2 from a list of locales when the line does not have a channel', () => {
    const channels: Channel[] = [createFakeChannel('ecommerce', ['en_US', 'fr_FR'])];
    const locales: Locale[] = [
      createFakeLocale('en_US', 'English'),
      createFakeLocale('fr_FR', 'French'),
      createFakeLocale('de_DE', 'German'),
    ];

    expect(getOptionsFromLocales(channels, locales, null)).toEqual({
      en_US: 'English',
      fr_FR: 'French',
      de_DE: 'German',
    });
  });

  test('I can create options for select2 from a list of locales restricted to the current channel', () => {
    const channels: Channel[] = [
      {
        ...createFakeChannel('ecommerce', ['en_US', 'fr_FR']),
        locales: [createFakeLocale('en_US', 'English'), createFakeLocale('fr_FR', 'French')],
      },
    ];
    const locales: Locale[] = [
      createFakeLocale('en_US', 'English'),
      createFakeLocale('fr_FR', 'French'),
      createFakeLocale('de_DE', 'German'),
    ];

    expect(getOptionsFromLocales(channels, locales, 'ecommerce')).toEqual({
      en_US: 'English',
      fr_FR: 'French',
    });
  });
});

describe('akeneoassetmanager/application/asset-upload/utils/select2.ts -> formatLocaleOption', () => {
  test('I can have a label fallback when select2 state is unexpected', () => {
    expect(formatLocaleOption({text: 'foo'})).toEqual('foo');
  });

  test('I can create pure html for a locale label with a flag', () => {
    expect(formatLocaleOption({id: 'en_US', text: 'English'})).toEqual(`
<span class="flag-language">
  <i class="flag flag-us"></i>
  <span class="language">English</span>
</span>
`);
  });
});
