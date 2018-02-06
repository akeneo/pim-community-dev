export const catalogLocaleChanged = (locale: string) => {
  return {type: 'LOCALE_CHANGED', locale, target: 'catalogLocale'};
};

export const uiLocaleChanged = (locale: string) => {
  return {type: 'LOCALE_CHANGED', locale, target: 'uiLocale'};
};

export const catalogChannelChanged = (channel: string) => {
  return {type: 'CHANNEL_CHANGED', channel, target: 'catalogChannel'};
};
