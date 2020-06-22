import {baseFetcher} from '@akeneo-pim-community/shared';
import {validateAnnouncement} from '../validator/announcement';
import {Announcement} from '../models/announcement';

const formatBackendUri = (limit: number, searchAfter: string | null) => {
  let backendUri = `/rest/announcements?limit=${limit}`;
  if (null !== searchAfter) {
    backendUri = backendUri.concat(`&search_after=${searchAfter}`);
  }

  return backendUri;
};

const fetchAnnouncements = async (searchAfter: string | null = null, limit: number = 10): Promise<Announcement[]> => {
  const route = formatBackendUri(limit, searchAfter);

  const jsonResponse = await baseFetcher(route);
  const announcements = jsonResponse.items;
  announcements.map(validateAnnouncement);

  return announcements;
};

export {fetchAnnouncements};
