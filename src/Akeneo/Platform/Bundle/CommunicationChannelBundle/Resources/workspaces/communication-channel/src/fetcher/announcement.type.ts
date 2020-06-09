import {Announcement} from '../models/announcement';

type AnnouncementFetcher = {
  fetchAll: () => Promise<Announcement[]>;
};

export {AnnouncementFetcher};
