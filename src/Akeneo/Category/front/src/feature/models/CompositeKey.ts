import {LocaleCode} from '@akeneo-pim-community/shared';
import {CategoryAttribute} from './Attribute';

// a composite key points on some attribute value in a category data structure
// it is of the form "<attribute code>|<attribute UUID>" with eventual suffix "|<locale>" if the attribute is localizable
export type CompositeKey = string;

// for 'attribute_code' in CategoryAttributeValueWrapper, where locale is never present
export type CompositeKeyWithoutLocale = string;

const COMPOSITE_KEY_SEPARATOR = '|';

export function buildCompositionKey(attribute: CategoryAttribute, localeCode: LocaleCode | null = null): CompositeKey {
  const {code, uuid} = attribute;
  const components = [code, uuid];

  if (localeCode) {
    components.push(localeCode);
  }

  return components.join(COMPOSITE_KEY_SEPARATOR);
}
