import {useState, useCallback, useEffect} from 'react';
import {baseFetcher} from '@akeneo-pim-community/shared';
import {Announcement} from './../models/announcement';
import {validateAnnouncement} from '../validator/announcement';

const useAnnouncements = (): {announcements: Announcement[] | null; updateAnnouncements: () => Promise<void>} => {
  const [announcements, setAnnouncements] = useState<Announcement[] | null>(null);
  const route = './bundles/akeneocommunicationchannel/__mocks__/serenity-updates.json';

  const updateAnnouncements = useCallback(async () => {
    const jsonResponse = await baseFetcher(route);

    const announcements = jsonResponse.data;

    try {
      announcements.map(validateAnnouncement);
    } catch (error) {
      setAnnouncements(null);
    }

    setAnnouncements(announcements);
  }, [setAnnouncements, route]);

  useEffect(() => {
    updateAnnouncements();
  }, []);

  return {announcements, updateAnnouncements};
};

export {useAnnouncements};
