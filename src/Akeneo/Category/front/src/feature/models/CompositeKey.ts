import {LocaleCode, ChannelCode} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';

// a composite key points on some attribute value in a category data structure
// it is of the form "<attribute code>|<attribute UUID>" with eventual suffix "|<locale>" if the attribute is localizable
export type CompositeKey = string;

// for 'attribute_code' in CategoryAttributeValueWrapper, where locale is never present
export type CompositeKeyWithoutLocale = string;

const COMPOSITE_KEY_SEPARATOR = '|';

export function buildCompositeKey(
  attribute: Attribute,
  channelCode: ChannelCode | null = null,
  localeCode: LocaleCode | null = null
): CompositeKey {
  const {code, uuid} = attribute;
  const components = [code, uuid];

  if (channelCode && attribute.is_scopable) {
    components.push(channelCode);
  }

  if (localeCode && attribute.is_localizable) {
    components.push(localeCode);
  }

  return components.join(COMPOSITE_KEY_SEPARATOR);
}
