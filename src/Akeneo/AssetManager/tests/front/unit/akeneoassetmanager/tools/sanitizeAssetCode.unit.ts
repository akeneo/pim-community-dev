import sanitizeAssetCode from 'akeneoassetmanager/tools/sanitizeAssetCode';

describe('akeneoassetmanager/tools/sanitizeAssetCode', () => {
  test('I remove spaces', () => {
    expect(sanitizeAssetCode('asset code with spaces')).toEqual('assetcodewithspaces');
  });

  test('I replace not alphanumeric characters by underscore', () => {
    expect(sanitizeAssetCode('asset.code-with,5;forbidden~character!')).toEqual('asset_code_with_5_forbidden_character_');
  });

  test('I don\'t change the case', () => {
    expect(sanitizeAssetCode('asset_code_fr_FR')).toEqual('asset_code_fr_FR');
  })
});
