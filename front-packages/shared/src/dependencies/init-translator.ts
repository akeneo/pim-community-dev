// @ts-ignore
import Translator from '../../legacy/translator';

// @todo
// const UserContext = require('pim/user-context');
// UserContext.get('uiLocale')

const initTranslator = {
  fetch: async () => {
    if (Translator.locale === 'en_US') {
      return Promise.resolve();
    }

    const response = await fetch('js/translation/' + 'en_US' + '.js');
    const messages = await response.json();

    Translator.fromJSON(messages);
  }
}

export {initTranslator};
