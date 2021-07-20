import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/line-factory';
import {createFakeAssetFamily, createFakeChannel, createFakeLocale} from '../tools';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

const line = {
  code: 'foo',
  locale: null,
  channel: null,
};

const assertLineCreatedMatchExpected = (test: {
  filename: string;
  assetFamily: AssetFamily;
  channels: Channel[];
  locales: Locale[];
  expected: any;
}) => {
  let result = createLineFromFilename(test.filename, test.assetFamily, test.channels, test.locales);
  expect(result).toMatchObject(test.expected);
  expect(result.filename).toBe(test.filename);
};

test('I can create a line from a filename not localizable and not scopable', () => {
  const assetFamily = createFakeAssetFamily(false, false);
  const channels: Channel[] = [];
  const locales: Locale[] = [];

  [
    {
      filename: 'foo.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
      },
    },
    {
      filename: 'Foo bar%20.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'Foobar_20',
      },
    },
    {
      filename: 'foo.jpg.pdf.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo_jpg_pdf',
      },
    },
  ].forEach(test => assertLineCreatedMatchExpected(test));
});

test('I can create a line from a filename localizable and not scopable', () => {
  const assetFamily = createFakeAssetFamily(true, false);
  const channels: Channel[] = [];
  const locales: Locale[] = [createFakeLocale('en_US')];

  [
    {
      filename: 'foo-en_US.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
      },
    },
    {
      filename: 'foo-en_US',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
      },
    },
    {
      filename: 'foo.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
      },
    },
  ].forEach(test => assertLineCreatedMatchExpected(test));
});

test('I can create a line from a filename not localizable and scopable', () => {
  const assetFamily = createFakeAssetFamily(false, true);
  const channels: Channel[] = [createFakeChannel('ecommerce')];
  const locales: Locale[] = [];

  [
    {
      filename: 'foo-ecommerce.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo-ecommerce',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        channel: null,
      },
    },
  ].forEach(test => assertLineCreatedMatchExpected(test));
});

test('I can create a line from a filename localizable and scopable', () => {
  const assetFamily = createFakeAssetFamily(true, true);
  const channels: Channel[] = [createFakeChannel('ecommerce', ['en_US'])];
  const locales: Locale[] = [createFakeLocale('en_US')];

  [
    {
      filename: 'foo-en_US-ecommerce.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo-en_US-ecommerce',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo--ecommerce.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo-en_US-.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: 'en_US',
        channel: null,
      },
    },
    {
      filename: 'foo--.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
        channel: null,
      },
    },
    {
      filename: 'foo.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo',
        locale: null,
        channel: null,
      },
    },
    {
      filename: 'foo5%-en_US-ecommerce.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo5_',
        locale: 'en_US',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo4 -en_US-ecommerce',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo4',
        locale: 'en_US',
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo3$--ecommerce.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo3_',
        locale: null,
        channel: 'ecommerce',
      },
    },
    {
      filename: 'foo2!-en_US-.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo2_',
        locale: 'en_US',
        channel: null,
      },
    },
    {
      filename: 'foo1 --.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo1',
        locale: null,
        channel: null,
      },
    },
  ].forEach(test => assertLineCreatedMatchExpected(test));
});

test('I can create a line from a filename but ignores invalid locale and channel', () => {
  const assetFamily = createFakeAssetFamily(true, true);
  const channels: Channel[] = [createFakeChannel('ecommerce', ['en_US'])];
  const locales: Locale[] = [createFakeLocale('en_US')];

  [
    {
      filename: 'foo-fr_FR-print.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo_fr_FR_print',
        locale: null,
        channel: null,
      },
    },
    {
      filename: 'foo-fr_FR.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo_fr_FR',
        locale: null,
        channel: null,
      },
    },
    {
      filename: 'foo--print.png',
      assetFamily: assetFamily,
      channels: channels,
      locales: locales,
      expected: {
        ...line,
        code: 'foo__print',
        locale: null,
        channel: null,
      },
    },
  ].forEach(test => assertLineCreatedMatchExpected(test));
});
