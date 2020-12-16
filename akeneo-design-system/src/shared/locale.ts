import {getEmoji} from './flag';

const getLocale = (localeCode: string, localeLabel?: string): string => {
  const emoji = getEmoji(localeCode);

  return `${emoji} ${localeLabel ?? localeCode.split('_')[0]}`;
};

export {getLocale};
