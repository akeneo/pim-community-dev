import {generateValueKey} from 'akeneoassetmanager/domain/model/asset/list-asset';

describe('akeneo > asset family > domain > model > asset --- list-asset', () => {
  test('I can get a value key from a value', () => {
    expect(
      generateValueKey({
        attribute: 'description_fingerprint',
        channel: 'ecommerce',
        locale: 'en_US',
      })
    ).toEqual('description_fingerprint_ecommerce_en_US');
  });
});
