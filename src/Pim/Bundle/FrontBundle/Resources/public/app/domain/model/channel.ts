import Locale, {createLocale} from 'pimfront/app/domain/model/locale';

interface LabelCollection {
  [locale: string]: string
}

export default interface Channel {
  code: string;
  labels: LabelCollection;
  locales: Locale[];
};

class ConcreteChannel {
  readonly code: string;
  readonly labels: LabelCollection;
  readonly locales: Locale[];

  public constructor (code: string, labels: LabelCollection, locales: Locale[] = []) {
    this.code = code;
    this.labels = labels;
    this.locales = locales;
  }
}

export const createChannel = (rawChannel: any): Channel => {
  return new ConcreteChannel(
    rawChannel.code,
    rawChannel.labels,
    rawChannel.locales.map((rawLocale: any) => createLocale(rawLocale))
  );
};
