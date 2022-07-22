import {Locale} from '../models/Locale';
import {useInfiniteChannels} from './useInfiniteChannels';

export const useScopedLocales = (channelCode: string| null): Locale[] => {
    const channelFilter = channelCode !== null ? {code: channelCode} : undefined;
    const {data: channels } = useInfiniteChannels(channelFilter);

    if (channelCode === null || channels === undefined || channels.length === 0) {
        return [];
    }

    return channels[0].locales;
};
