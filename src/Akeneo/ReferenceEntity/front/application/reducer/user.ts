import Channel from 'akeneoreferenceentity/domain/model/channel';
import Locale from 'akeneoreferenceentity/domain/model/locale';

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
  action: {type: string; target: string; locale?: string; channel?: string; channels?: Channel[]}
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
      if (!Array.isArray(action.channels)) {
        throw new InvalidArgumentError('The user reducer needs an array as channels for the event "CHANNEL_CHANGED"');
      }
      const catalogLocale = state.catalogLocale;

      const newLocale = getCatalogLocale(action.channels, action.channel, catalogLocale);

      state = {...state, [`${action.target}Channel`]: action.channel, [`${action.target}Locale`]: newLocale};
      break;
    default:
      break;
  }

  return state;
};

/**
 * When there is the channel and the locale switcher on a page, the locale list is defined by the channel. Indeed, each channel contains a list of locales. So if you have the following channels:
 * - ecommerce
 *   - en_US
 *   - fr_FR
 * - mobile
 *   - de_DE
 *   - fr_FR
 *
 * If we are on ecommerce english and switch to mobile. As the locale en_US doesn't exists on mobile, we need to switch to the first locale of the mobile channel
 */
const getCatalogLocale = (channels: Channel[], channelCode: string, localeCode: string) => {
  const channel = channels.find((channel: Channel) => channel.code === channelCode);

  if (undefined === channel || channel.locales.map((locale: Locale) => locale.code).includes(localeCode)) {
    return localeCode;
  }

  return channel.locales[0].code;
};
