import {useState, useCallback, useEffect} from 'react';
import {baseFetcher, useIsMounted} from '@akeneo-pim-community/shared';
import {validateHasNewAnnouncements} from '../validator/hasNewAnnouncements';
import {useMediator} from '@akeneo-pim-community/legacy-bridge';

const useHasNewAnnouncements = (): (() => void) => {
  const mediator = useMediator();
  const [hasNewAnnouncements, setHasNewAnnouncements] = useState<boolean>(false);
  const route = '/rest/new_announcements';
  const isMounted = useIsMounted();

  const handleHasNewAnnouncements = useCallback(async () => {
    const data = await baseFetcher(route);
    validateHasNewAnnouncements(data);

    if (isMounted()) {
      setHasNewAnnouncements(data.status);
    }
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
