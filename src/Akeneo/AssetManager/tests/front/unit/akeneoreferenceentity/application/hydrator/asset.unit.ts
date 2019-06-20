import hydrator from 'akeneoassetmanager/application/hydrator/asset';

describe('akeneo > asset family > application > hydrator --- asset', () => {
  test('I can hydrate a new asset', () => {
    expect(
      hydrator({
        identifier: 'designer_starck_fingerprint',
        asset_family_identifier: 'designer',
        code: 'starck',
        labels: {en_US: 'Stark'},
        image: null,
        values: [],
      })
    );
  });

  test('It throw an error if I pass a malformed asset', () => {
    expect(() => hydrator({})).toThrow();
    expect(() => hydrator({labels: {}})).toThrow();
    expect(() => hydrator({identifier: 'starck'})).toThrow();
    expect(() => hydrator({assetFamilyIdentifier: 'designer'})).toThrow();
  });
});
