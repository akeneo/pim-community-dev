// @ts-ignore
import Translator from '../../legacy/translator';
import {userContext} from './user-context';

const initTranslator = {
  fetch: async () => {
    const locale = userContext.get('uiLocale');

    if (Translator.locale === locale) {
      return Promise.resolve();
    }

    const response = await fetch('js/translation/' + locale + '.js');
    const messages = await response.json();

    Translator.fromJSON(messages);

    return Promise.resolve();
  },
};

export {initTranslator};
