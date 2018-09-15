import ChannelReference from 'akeneoenrichedentity/domain/model/channel-reference';
import LocaleReference from 'akeneoenrichedentity/domain/model/locale-reference';
import Data from 'akeneoenrichedentity/domain/model/record/data';
import Attribute, {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {CommonConcreteAttribute} from 'akeneoenrichedentity/domain/model/attribute/common';

export interface NormalizedValue {
  attribute: NormalizedAttribute;
  channel: string | null;
  locale: string | null;
  data: any;
}

class InvalidTypeError extends Error {}

export default class Value {
  private constructor(
    private attribute: Attribute,
    private channel: ChannelReference,
    private locale: LocaleReference,
    private data: Data
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

  public isEmpty(): boolean {
    return this.data.isEmpty();
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
}

export const createValue = Value.create;
