import routing from 'pimui/js/fos-routing-wrapper';
import {is} from 'typescript-is';

type DateContextData = {
  date: {
    format: string;
    defaultFormat: string;
  };
  time: {
    format: string;
    defaultFormat: string;
  };
  timezone: string;
  language: string;
  twelveHourFormat: boolean;
};

class DateContext {
  private data: DateContextData | null = null;

  async initialize() {
    const response = await fetch(routing.generate('pim_localization_format_date'));

    const data = await response.json();
    if (!is<DateContextData>(data)) throw new Error('DateContextData are not valid');

    this.data = data;
  }

  get(key: keyof DateContextData) {
    if (null === this.data) throw new Error('Cannot access Datecontext before initialization.');

    return this.data[key];
  }
}

const dateContext = new DateContext();

export {dateContext};
