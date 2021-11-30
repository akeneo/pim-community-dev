import {Channel, useIsMounted} from '@akeneo-pim-community/shared';
import {useFetchers} from '../contexts';
import {useState, useEffect} from 'react';

const useChannels = (): Channel[] => {
  const channelFetcher = useFetchers().channel;
  const [channels, setChannels] = useState<Channel[]>([]);
  const isMounted = useIsMounted();

  useEffect(() => {
    channelFetcher.fetchAll().then((channels: Channel[]) => {
      if (!isMounted()) return;

      setChannels(channels);
    });
  }, [channelFetcher, isMounted]);

  return channels;
};

export {useChannels};
