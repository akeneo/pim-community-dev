import {useMemo} from 'react';
import {Channel} from '../models/Channel';

export const useChannelsWithSelectedChannel = (selectedChannel?: Channel | null, results?: Channel[]): Channel[] => {
    return useMemo(() => {
        if (!selectedChannel) {
            return results || [];
        }

        if (results === undefined || results.length === 0) {
            return [selectedChannel];
        }

        if (!results.find(channel => channel.code === selectedChannel.code)) {
            results.push(selectedChannel);
        }

        return results;
    }, [selectedChannel, results]);
};
