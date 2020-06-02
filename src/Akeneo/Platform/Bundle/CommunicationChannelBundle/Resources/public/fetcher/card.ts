import {Card} from '../model/card';

type CardFetcher = {
  fetchAll: () => Promise<Card[]>;
};

class CardFetcherImplementation {
  static jsonFilePath: string = './bundles/akeneocommunicationchannel/fetcher/__mocks__/serenity-updates-sample.json';

  static async fetchAll() {
    const response = await fetch(this.jsonFilePath);

    return await response.json();
  }
}

export {CardFetcher, CardFetcherImplementation};
