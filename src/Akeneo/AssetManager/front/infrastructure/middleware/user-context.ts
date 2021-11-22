import {UserContext} from '@akeneo-pim-community/shared';

export default (userContext: UserContext) => () => (next: any) => (action: any) => {
  if ('LOCALE_CHANGED' === action.type && action.target === 'catalog') {
    userContext.set('catalogLocale', action.locale, {});
  }

  if ('CHANNEL_CHANGED' === action.type) {
    userContext.set('catalogScope', action.channel, {});
  }

  return next(action);
};
