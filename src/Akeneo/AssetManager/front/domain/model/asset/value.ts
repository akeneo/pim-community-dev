import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import {ConcreteAttribute, Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {NormalizedAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';

type NormalizedContext = {
  labels: {
    [assetCode: string]: {
      [localeCode: string]: string;
    };
  };
};

/**
 * @api
 */
export type NormalizedValue = {
  attribute: NormalizedAttribute;
  channel: ChannelReference;
  locale: LocaleReference;
  data: any;
  context?: NormalizedContext;
};

export type NormalizedMinimalValue = {
  attribute: NormalizedAttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
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
    if (!(data instanceof Data)) {
      throw new InvalidTypeError('Value expect ValueData as data argument');
    }
    if (channelReferenceIsEmpty(channel) && attribute.valuePerChannel) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have a non empty channel reference`
      );
    }
    if (!channelReferenceIsEmpty(channel) && !attribute.valuePerChannel) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have an empty channel reference`
      );
    }
    if (localeReferenceIsEmpty(locale) && attribute.valuePerLocale) {
      throw new InvalidTypeError(
        `The value for attribute "${attribute.getCode().stringValue()}" should have a non empty locale reference`
      );
    }
    if (!localeReferenceIsEmpty(locale) && !attribute.valuePerLocale) {
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
      channelReferenceAreEqual(this.channel, value.channel) &&
      localeReferenceAreEqual(this.locale, value.locale) &&
      this.attribute.equals(value.attribute)
    );
  }

  public static create(attribute: Attribute, channel: ChannelReference, locale: LocaleReference, data: Data): Value {
    return new Value(attribute, channel, locale, data);
  }

  public normalize(): NormalizedValue {
    return {
      attribute: this.attribute.normalize(),
      channel: this.channel,
      locale: this.locale,
      data: this.data.normalize(),
    };
  }

  public normalizeMinimal(): NormalizedMinimalValue {
    return {
      attribute: this.attribute.identifier.normalize(),
      channel: this.channel,
      locale: this.locale,
      data: this.data.isEmpty() ? null : this.data.normalize(),
    };
  }
}

export default Value;
export const createValue = Value.create;
