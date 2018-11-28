const userContext = require('pim/user-context');

export default () => () => (next: any) => (action: any) => {
  if ('LOCALE_CHANGED' === action.type && action.target === 'catalog') {
    userContext.set('catalogLocale', action.locale);
  }

  if ('CHANNEL_CHANGED' === action.type) {
    userContext.set('catalogScope', action.channel);
  }

  return next(action);
};
