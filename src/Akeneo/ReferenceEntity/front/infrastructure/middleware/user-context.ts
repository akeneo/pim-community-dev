const userContext = require('pim/user-context');

export default () => () => (next: any) => (action: any) => {
  if ('LOCALE_CHANGED' === action.type) {
    userContext.set('catalog_default_locale', action.locale);
  }

  if ('CHANNEL_CHANGED' === action.type) {
    userContext.set('catalog_default_scope', action.channel);
  }

  return next(action);
};
