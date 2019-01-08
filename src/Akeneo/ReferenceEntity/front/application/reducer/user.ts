export interface UserState {
  defaultCatalogLocale: string;
  catalogLocale: string;
  catalogChannel: string;
  uiLocale: string;
}

class InvalidArgumentError extends Error {}

export default (
  state: UserState = {
    defaultCatalogLocale: '',
    catalogLocale: '',
    catalogChannel: '',
    uiLocale: '',
  },
  action: {type: string; target: string; locale?: string; channel?: string}
): UserState => {
  switch (action.type) {
    case 'DEFAULT_LOCALE_CHANGED':
      if ('string' !== typeof action.locale) {
        throw new InvalidArgumentError(
          'The user reducer needs a string as locale for the event "DEFAULT_LOCALE_CHANGED"'
        );
      }
      state = {...state, [`${action.target}Locale`]: action.locale};
      break;
    case 'LOCALE_CHANGED':
      if ('string' !== typeof action.locale) {
        throw new InvalidArgumentError('The user reducer needs a string as locale for the event "LOCALE_CHANGED"');
      }
      state = {...state, [`${action.target}Locale`]: action.locale};
      break;
    case 'CHANNEL_CHANGED':
      if ('string' !== typeof action.channel) {
        throw new InvalidArgumentError('The user reducer needs a string as channel for the event "CHANNEL_CHANGED"');
      }
      state = {...state, [`${action.target}Channel`]: action.channel};
      break;
    default:
      break;
  }

  return state;
};
