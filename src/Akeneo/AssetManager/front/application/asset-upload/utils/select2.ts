import Channel, {getChannelLabel} from 'akeneoassetmanager/domain/model/channel';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';

type Select2Options = {
  [value: string]: string;
};

export const getOptionsFromChannels = (channels: Channel[], locale: LocaleCode): Select2Options => {
  return channels.reduce((results: Select2Options, channel: Channel) => {
    results[channel.code] = getChannelLabel(channel, locale);
    return results;
  }, {});
};

/**
 * If there is no channel in the line, fallback on all locales.
 * If there is a channel, only returns locales from this channel.
 */
export const getOptionsFromLocales = (channels: Channel[], locales: Locale[], line: Line): Select2Options => {
  if (null === line.channel) {
    return locales.reduce((results: Select2Options, locale: Locale) => {
      results[locale.code] = locale.label;
      return results;
    }, {});
  }

  const channel = channels.find((channel: Channel) => channel.code === line.channel) as Channel;

  return channel.locales.reduce((results: Select2Options, locale: Locale) => {
    results[locale.code] = locale.label;
    return results;
  }, {});
};

export const formatLocaleOption = (state: any): string => {
  if (!state.id) return state.text;

  const info = state.id.split('_');
  const flag = info[1].toLowerCase();
  const language = state.text;

  return `
<span class="flag-language">
  <i class="flag flag-${flag}"></i>
  <span class="language">${language}</span>
</span>
`;
};
