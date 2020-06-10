import {validateAnnouncement} from '../validator/announcement';

class AnnouncementFetcher {
  static jsonFilePath: string = './bundles/akeneocommunicationchannel/__mocks__/serenity-updates.json';

  static async fetchAll() {
    const response = await fetch(this.jsonFilePath);

    const jsonResponse = await response.json();
    const announcements = jsonResponse.data;

    return announcements.map(validateAnnouncement);
  }
}

export {AnnouncementFetcher};
