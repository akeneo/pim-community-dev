import {NormalizedAttribute, Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';

const hydrator = (denormalize: (normalizedAttribute: NormalizedAttribute) => Attribute) => (
  normalizedAttribute: any
): Attribute => {
  const expectedKeys = [
    'identifier',
    'asset_family_identifier',
    'code',
    'labels',
    'is_required',
    'value_per_locale',
    'value_per_channel',
    'type',
  ];
  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');

  return denormalize(normalizedAttribute);
};

export {hydrator};
