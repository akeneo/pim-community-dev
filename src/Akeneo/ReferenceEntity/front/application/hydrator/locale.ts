import Locale, {denormalizeLocale} from 'akeneoreferenceentity/domain/model/locale';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';

export const hydrator = (denormalizeLocale: (normalizedLocale: any) => Locale) => (normalizedLocale: any): Locale => {
  const expectedKeys = ['code', 'label', 'region', 'language'];

  validateKeys(normalizedLocale, expectedKeys, 'The provided raw locale seems to be malformed.');

  return denormalizeLocale(normalizedLocale);
};

export default hydrator(denormalizeLocale);
