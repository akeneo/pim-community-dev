import Locale, {denormalizeLocale, ConcreteLocale} from 'akeneoenrichedentity/domain/model/locale';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

export default interface Channel {
  code: string;
  labelCollection: LabelCollection;
  locales: Locale[];
  getLabel(localeCode: string): string;
}
class InvalidTypeError extends Error {}

export class ConcreteChannel {
  public constructor(readonly code: string, readonly labelCollection: LabelCollection, readonly locales: Locale[]) {
    if ('string' !== typeof code) {
      throw new InvalidTypeError('Channel expect a string as code to be created');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidTypeError('Channel expect a LabelCollection as second argument');
    }
    Object.keys(locales).forEach((localeKey: string) => {
      if (!(locales[localeKey as any] instanceof ConcreteLocale)) {
        throw new InvalidTypeError('Channel expect a Locale collection as third argument');
      }
    });
    Object.freeze(this);
  }

  getLabel(localeCode: string) {
    return this.labelCollection.hasLabel(localeCode) ? this.labelCollection.getLabel(localeCode) : `[${this.code}]`;
  }
}

export const denormalizeChannel = (rawChannel: any): Channel => {
  return new ConcreteChannel(
    rawChannel.code,
    createLabelCollection(rawChannel.labels),
    rawChannel.locales.map((rawLocale: any) => denormalizeLocale(rawLocale))
  );
};
