import {useState, useCallback, useEffect} from 'react';
import {baseFetcher} from '@akeneo-pim-community/shared';
import {Announcement} from './../models/announcement';
import {validateAnnouncement} from '../validator/announcement';

const useAnnouncements = (): {
  data: Announcement[];
  hasError: boolean;
} => {
  const [announcements, setAnnouncements] = useState<{
    data: Announcement[];
    hasError: boolean;
  }>({
    data: [],
    hasError: false,
  });
  const route = '/rest/announcements';

  const updateAnnouncements = useCallback(async () => {
    try {
      const jsonResponse = await baseFetcher(route);
      const announcements = jsonResponse.items;
      announcements.map(validateAnnouncement);

      setAnnouncements({data: announcements, hasError: false});
    } catch (error) {
      setAnnouncements({data: [], hasError: true});
    }
  }, [route, setAnnouncements]);

  useEffect(() => {
    updateAnnouncements();
  }, []);

  return announcements;
};

export {useAnnouncements};
