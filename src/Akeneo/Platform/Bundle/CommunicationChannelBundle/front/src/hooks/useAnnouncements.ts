import {useState, useCallback, useEffect} from 'react';
import {baseFetcher} from '@akeneo-pim-community/shared';
import {Announcement} from './../models/announcement';
import {validateAnnouncement} from '../validator/announcement';

const useAnnouncements = (): {
  data: Announcement[];
  hasError: boolean;
} => {
  const [announcementResponse, setAnnouncementResponse] = useState<{
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

      setAnnouncementResponse({data: announcements, hasError: false});
    } catch (error) {
      setAnnouncementResponse({data: [], hasError: true});
    }
  }, [route, setAnnouncementResponse]);

  useEffect(() => {
    updateAnnouncements();
  }, []);

  return announcementResponse;
};

export {useAnnouncements};
