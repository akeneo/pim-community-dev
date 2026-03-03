import {useState, useCallback, useEffect} from 'react';
import {validateHasNewAnnouncements} from '../validator/hasNewAnnouncements';
import {useIsMounted, useMediator} from '@akeneo-pim-community/shared';

const useHasNewAnnouncements = (): (() => void) => {
  const mediator = useMediator();
  const [hasNewAnnouncements, setHasNewAnnouncements] = useState<boolean>(false);
  const route = '/rest/new_announcements';
  const isMounted = useIsMounted();

  const handleHasNewAnnouncements = useCallback(async () => {
    const response = await fetch(route);
    const data = await response.json();
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
