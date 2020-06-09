import {Announcement} from '../models/announcement';
import {validateAnnouncement} from '../validator/announcement';

type AnnouncementFetcher = {
  fetchAll: () => Promise<Announcement[]>;
};

class AnnouncementFetcherImplementation {
  static jsonFilePath: string = './bundles/akeneocommunicationchannel/__mocks__/serenity-updates-sample.json';

  static async fetchAll() {
    const response = await fetch(this.jsonFilePath);

    const jsonResponse = await response.json();
    const cards = jsonResponse['data'];

    return cards.map(validateAnnouncement);
  }
}

export {AnnouncementFetcher, AnnouncementFetcherImplementation};
