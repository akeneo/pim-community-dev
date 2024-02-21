import {validateAnnouncement} from '../validator/announcement';
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

  const response = await fetch(route);
  const jsonResponse = await response.json();
  const announcements = jsonResponse.items;
  announcements.map(validateAnnouncement);

  return announcements;
};

export {fetchAnnouncements};
