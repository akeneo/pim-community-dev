import {baseFetcher} from '../shared/src/fetcher';
import {Announcement} from '../models/announcement';

const formatBackendUri = (searchAfter: string | null) => {
  let backendUri = `/rest/announcements`;
  if (null !== searchAfter) {
    backendUri = backendUri.concat(`?search_after=${searchAfter}`);
  }

  return backendUri;
};

const fetchAnnouncements = async (searchAfter: string | null = null): Promise<Announcement[]> => {
  const route = formatBackendUri(searchAfter);

  const jsonResponse = await baseFetcher(route);
  const announcements = jsonResponse.items;

  return announcements;
};

export {fetchAnnouncements};
