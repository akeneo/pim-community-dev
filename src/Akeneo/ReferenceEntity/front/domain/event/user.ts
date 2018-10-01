export const catalogLocaleChanged = (locale: string) => {
  return {type: 'LOCALE_CHANGED', locale, target: 'catalog'};
};

export const uiLocaleChanged = (locale: string) => {
  return {type: 'LOCALE_CHANGED', locale, target: 'ui'};
};

export const catalogChannelChanged = (channel: string) => {
  return {type: 'CHANNEL_CHANGED', channel, target: 'catalog'};
};
