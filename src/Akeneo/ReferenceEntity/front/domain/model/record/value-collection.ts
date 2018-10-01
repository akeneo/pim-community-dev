import Value, {NormalizedValue, NormalizedMinimalValue} from 'akeneoreferenceentity/domain/model/record/value';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';

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

export const createValueCollection = ValueCollection.create;
