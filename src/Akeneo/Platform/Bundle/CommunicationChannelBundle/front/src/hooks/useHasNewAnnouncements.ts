import {useState, useEffect, useCallback} from 'react';
import {baseFetcher} from '@akeneo-pim-community/shared';
import {validateHasNewAnnouncements} from '../validator/hasNewAnnouncements';

const useHasNewAnnouncements = () => {
  const [hasNewAnnouncements, setHasNewAnnouncements] = useState<boolean | null>(null);
  const route = '/rest/new_announcements';

  const updateHasNewAnnouncements = useCallback(async () => {
    const data = await baseFetcher(route);
    validateHasNewAnnouncements(data);

    setHasNewAnnouncements(data.status);
  }, []);

  useEffect(() => {
    updateHasNewAnnouncements();
  }, []);

  return hasNewAnnouncements;
};

export {useHasNewAnnouncements};
