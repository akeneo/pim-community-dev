import ChannelReference, {NormalizedChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference, {NormalizedLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import Data from 'akeneoreferenceentity/domain/model/record/data';
import {
  ConcreteAttribute,
  Attribute,
  NormalizedAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {NormalizedAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';

type NormalizedContext = {
  labels: {
    [recordCode: string]: {
      [localeCode: string]: string;
    };
  };
};

/**
 * @api
 */
export type NormalizedValue = {
  attribute: NormalizedAttribute;
  channel: NormalizedChannelReference;
  locale: NormalizedLocaleReference;
  data: any;
  context?: NormalizedContext;
};

export type NormalizedMinimalValue = {
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
    if (!(attribute instanceof ConcreteAttribute)) {
      throw new InvalidTypeError('Value expect ConcreteAttribute as attribute argument');
    }
    if (!(channel instanceof ChannelReference)) {
      throw new InvalidTypeError('Value expect ChannelReference as channel argument');
    }
    if (!(locale instanceof LocaleReference)) {
      throw new InvalidTypeError('Value expect LocaleReference as locale argument');
    }
    if (!(data instanceof Data)) {
      throw new InvalidTypeError('Value expect ValueData as data argument');
    }
    if (channel.isEmpty() && attribute.valuePerChannel) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have a non empty channel reference`
      );
    }
    if (!channel.isEmpty() && !attribute.valuePerChannel) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have an empty channel reference`
      );
    }
    if (locale.isEmpty() && attribute.valuePerLocale) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have a non empty locale reference`
      );
    }
    if (!locale.isEmpty() && !attribute.valuePerLocale) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have an empty locale reference`
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

  public isComplete(): boolean {
    return this.attribute.isRequired && !this.data.isEmpty();
  }

  public isRequired(): boolean {
    return this.attribute.isRequired;
  }

  public equals(value: Value): boolean {
    return (
      this.channel.equals(value.channel) && this.locale.equals(value.locale) && this.attribute.equals(value.attribute)
    );
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

  public normalizeMinimal(): NormalizedMinimalValue {
    return {
      attribute: this.attribute.identifier.normalize(),
      channel: this.channel.normalize(),
      locale: this.locale.normalize(),
      data: this.data.isEmpty() ? null : this.data.normalize(),
    };
  }
}

export default Value;
export const createValue = Value.create;
