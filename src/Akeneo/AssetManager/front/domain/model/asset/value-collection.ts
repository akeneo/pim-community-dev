import Value, {NormalizedValue, NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

class InvalidTypeError extends Error {}

export default class ValueCollection {
  private constructor(private values: Value[]) {
    values.forEach((value: Value) => {
      if (!(value instanceof Value)) {
        throw new InvalidTypeError('ValueCollection expect only Value objects as argument');
      }
    });

    Object.freeze(this);
  }

  public static create(values: Value[]): ValueCollection {
    return new ValueCollection(values);
  }

  public getValuesForChannelAndLocale(channel: ChannelReference, locale: LocaleReference): Value[] {
    return this.values.filter(
      (value: Value) =>
        (value.channel.isEmpty() || value.channel.equals(channel)) &&
        (value.locale.isEmpty() || value.locale.equals(locale))
    );
  }

  public normalize(): NormalizedValue[] {
    return this.values.map((value: Value) => value.normalize());
  }

  public normalizeMinimal(): NormalizedMinimalValue[] {
    return this.values.map((value: Value) => value.normalizeMinimal());
  }
}

export const generateKey = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  let key = attributeIdentifier.stringValue();
  key = !channel.isEmpty() ? `${key}_${channel.stringValue()}` : key;
  key = !locale.isEmpty() ? `${key}_${locale.stringValue()}` : key;

  return key;
};

export const createValueCollection = ValueCollection.create;
