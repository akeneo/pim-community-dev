import {useState, useCallback, useEffect} from 'react';
import {Announcement} from './../models/announcement';
import {AnnouncementFetcher} from './../fetcher/announcement';

const useAnnouncements = (
  announcementFetcher: AnnouncementFetcher
): {announcements: Announcement[] | null; fetchAnnouncements: () => Promise<void>} => {
  const [announcements, setAnnouncements] = useState<Announcement[] | null>(null);

  const fetchAnnouncements = useCallback(async () => {
    setAnnouncements(await announcementFetcher.fetchAll());
  }, [setAnnouncements]);

  useEffect(() => {
    fetchAnnouncements();
  }, []);

  return {announcements, fetchAnnouncements};
};

export {useAnnouncements};
