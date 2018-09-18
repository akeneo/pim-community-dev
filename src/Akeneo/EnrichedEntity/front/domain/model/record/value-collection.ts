import Value, {NormalizedValue} from 'akeneoenrichedentity/domain/model/record/value';
import ChannelReference from 'akeneoenrichedentity/domain/model/channel-reference';
import LocaleReference from 'akeneoenrichedentity/domain/model/locale-reference';

class InvalidTypeError extends Error {}

export default class ValueCollection {
  private constructor(private values: Value[]) {
    Object.values(values).forEach((value: Value) => {
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
        (value.getChannel().isEmpty() || value.getChannel().equals(channel)) &&
        (value.getLocale().isEmpty() || value.getLocale().equals(locale))
    );
  }

  public normalize(): NormalizedValue[] {
    return this.values.map((value: Value) => value.normalize());
  }

  public normalizeMinimal(): NormalizedValue[] {
    return this.values.filter((value: Value) => !value.isEmpty()).map((value: Value) => value.normalizeMinimal());
  }
}

export const createValueCollection = ValueCollection.create;
