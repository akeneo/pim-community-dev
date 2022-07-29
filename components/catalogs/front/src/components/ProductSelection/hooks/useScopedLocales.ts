import {Locale} from '../models/Locale';
import {useInfiniteChannels} from './useInfiniteChannels';

export const useScopedLocales = (channelCode: string | null): Locale[] => {
    const {data: channels, isLoading, isError, error} = useInfiniteChannels({code: channelCode});

    if (isError) {
        throw new Error(error as string);
    }

    if (isLoading || channelCode === null) {
        return [];
    }

    return channels?.[0]?.locales ?? [];
};
