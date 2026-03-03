import {Channel, getLabel} from '@akeneo-pim-community/shared';

/**
 * Get the translation of a channel according to his code and a given locale.
 * @param {Channel[]} channelList The channel list to get information.
 * @param {string | null} channelCode The channel code to get his translation.
 * @param {string} locale The locale code to translate the channel.
 *
 * @return {string | null} The channel translated or null if no translation.
 */
export function getChannelTranslation(
  channelList: Channel[],
  channelCode: string | null,
  locale: string
): string | null {
  const retrievedChannel: Channel | null = channelList.find(channel => channel.code === channelCode) ?? null;
  return retrievedChannel && getLabel(retrievedChannel.labels, locale, `${channelCode}`);
}
