import ChannelReference, {NormalizedChannelReference} from 'akeneoenrichedentity/domain/model/channel-reference';
import LocaleReference, {NormalizedLocaleReference} from 'akeneoenrichedentity/domain/model/locale-reference';
import Data from 'akeneoenrichedentity/domain/model/record/data';
import Attribute, {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {CommonConcreteAttribute} from 'akeneoenrichedentity/domain/model/attribute/common';
import {NormalizedAttributeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';

export type NormalizedValue =
  | {
      attribute: NormalizedAttribute;
      channel: NormalizedChannelReference;
      locale: NormalizedLocaleReference;
      data: any;
    }
  | {
      attribute: NormalizedAttributeIdentifier;
      channel: NormalizedChannelReference;
      locale: NormalizedLocaleReference;
      data: any;
    };

class InvalidTypeError extends Error {}

class Value {
  private constructor(
    readonly attribute: Attribute,
    readonly channel: ChannelReference,
    readonly locale: LocaleReference,
    readonly data: Data
  ) {
    if (!(attribute instanceof CommonConcreteAttribute)) {
      throw new InvalidTypeError('Value expect CommonConcreteAttribute as argument');
    }
    if (!(channel instanceof ChannelReference)) {
      throw new InvalidTypeError('Value expect ChannelReference as argument');
    }
    if (!(locale instanceof LocaleReference)) {
      throw new InvalidTypeError('Value expect LocaleReference as argument');
    }
    if (!(data instanceof Data)) {
      throw new InvalidTypeError('Value expect ValueData as argument');
    }
    if (channel.isEmpty() && attribute.valuePerChannel) {
      throw new InvalidTypeError(
        `The value for attribute ${attribute.getCode()} should have a non empty channel reference`
      );
    }
    if (locale.isEmpty() && attribute.valuePerLocale) {
      throw new InvalidTypeError(
        `The value for attribute ${attribute.getCode()} should have a non empty locale reference`
      );
    }

    Object.freeze(this);
  }

  setData(data: Data) {
    return Value.create(this.attribute, this.channel, this.locale, data);
  }

  public isEmpty(): boolean {
    return this.data.isEmpty();
  }

  public equals(value: Value): boolean {
    return (
      this.channel.equals(value.channel) && this.locale.equals(value.locale) && this.attribute.equals(value.attribute)
    );
  }

  getChannel(): ChannelReference {
    return this.channel;
  }

  getLocale(): LocaleReference {
    return this.locale;
  }

  getAttribute(): Attribute {
    return this.attribute;
  }

  public static create(attribute: Attribute, channel: ChannelReference, locale: LocaleReference, data: Data): Value {
    return new Value(attribute, channel, locale, data);
  }

  public normalize(): NormalizedValue {
    return {
      attribute: this.attribute.normalize(),
      channel: this.channel.normalize(),
      locale: this.locale.normalize(),
      data: this.data.normalize(),
    };
  }

  public normalizeMinimal(): NormalizedValue {
    return {
      attribute: this.attribute.identifier.normalize(),
      channel: this.channel.normalize(),
      locale: this.locale.normalize(),
      data: this.data.normalize(),
    };
  }
}

export default Value;
export const createValue = Value.create;
