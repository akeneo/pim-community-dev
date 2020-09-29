import {useState, useCallback, useEffect} from 'react';
import {baseFetcher} from '../shared/src/fetcher';
import {useMediator} from '../legacy-bridge/src/hooks';

const useHasNewAnnouncements = (): (() => void) => {
  const mediator = useMediator();
  const [hasNewAnnouncements, setHasNewAnnouncements] = useState<boolean>(false);
  const route = '/rest/new_announcements';

  const handleHasNewAnnouncements = useCallback(async () => {
    const data = await baseFetcher(route);

    setHasNewAnnouncements(data.status);
  }, []);

  useEffect(() => {
    if (hasNewAnnouncements) {
      sessionStorage.setItem('communication_channel_has_new_announcements', JSON.stringify(hasNewAnnouncements));
      mediator.trigger('communication-channel:menu:add_coloured_dot');
    } else {
      sessionStorage.setItem('communication_channel_has_new_announcements', JSON.stringify(hasNewAnnouncements));
      mediator.trigger('communication-channel:menu:remove_coloured_dot');
    }
  }, [hasNewAnnouncements]);

  return handleHasNewAnnouncements;
};

export {useHasNewAnnouncements};
