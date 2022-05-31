// @ts-ignore
import Translator from '../../legacy/translator';

// @todo
// const UserContext = require('pim/user-context');
// UserContext.get('uiLocale')

const initTranslator = {
  fetch: async () => {
    const response = await fetch('js/translation/' + 'en_US' + '.js');
    const messages = await response.json();

    console.log(Translator);
    Translator.fromJSON(messages);
  }
}

export {initTranslator};
