'use strict';

define(['jquery', 'pim/user-context', 'translator-lib'], function ($, UserContext, Translator) {
  return {
    fetch: function () {
      return fetch('js/translation/' + UserContext.get('uiLocale') + '.js').then(async function (messages) {
        Translator.fromJSON(await messages.json());
      });
    },
  };
});
