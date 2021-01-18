import {DictionaryApp} from '@akeneo-pim-ee/data-quality-insights';
import BaseView from 'pimui/js/view/base';

class DictionaryAppView extends BaseView {
  private localeCode: string;

  public setLocaleCode(localeCode: string) {
    this.localeCode = localeCode;

    return this;
  }

  public render() {
    this.renderReact(
      DictionaryApp,
      // @ts-ignore
      {localeCode: this.localeCode},
      document.getElementById('dqi-dictionary-container') as Element
    );

    return this;
  }
}

export = DictionaryAppView;
