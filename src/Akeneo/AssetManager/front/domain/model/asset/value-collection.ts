import Value from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceAreEqual,
  channelReferenceStringValue,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceAreEqual,
  localeReferenceStringValue,
} from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';

export const getValueForChannelAndLocaleFilter = (channel: ChannelReference, locale: LocaleReference) => (
  value: Value
): boolean =>
  (channelReferenceIsEmpty(value.channel) || channelReferenceAreEqual(value.channel, channel)) &&
  (localeReferenceIsEmpty(value.locale) || localeReferenceAreEqual(value.locale, locale));

export const getValueForAttributeIdentifierFilter = (attributeIdentifier: AttributeIdentifier) => (value: Value) =>
  value.attribute.identifier === attributeIdentifier;

export const getValueFilter = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  const attributeFilter = getValueForAttributeIdentifierFilter(attributeIdentifier);
  const channelAndLocaleFilter = getValueForChannelAndLocaleFilter(channel, locale);

  return (value: Value) => attributeFilter(value) && channelAndLocaleFilter(value);
};

export default class ValueCollection {
  private constructor(readonly values: Value[]) {
    Object.freeze(this);
  }

  public static create(values: Value[]): ValueCollection {
    return new ValueCollection(values);
  }

  public getValuesForChannelAndLocale(channel: ChannelReference, locale: LocaleReference): Value[] {
    return this.values.filter(getValueForChannelAndLocaleFilter(channel, locale));
  }

  public normalize(): Value[] {
    return this.values;
  }

  public normalizeMinimal(): Value[] {
    return this.values;
  }
}

export const generateKey = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  let key = attributeIdentifierStringValue(attributeIdentifier);
  key = !channelReferenceIsEmpty(channel) ? `${key}_${channelReferenceStringValue(channel)}` : key;
  key = !localeReferenceIsEmpty(locale) ? `${key}_${localeReferenceStringValue(locale)}` : key;

  return key;
};

export const createValueCollection = ValueCollection.create;
