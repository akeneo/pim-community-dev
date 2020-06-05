import {Card} from './../models/card';
import {validateCard} from './../validator/card';

type CardFetcher = {
  fetchAll: () => Promise<Card[]>;
};

class CardFetcherImplementation {
  static jsonFilePath: string = './bundles/akeneocommunicationchannel/__mocks__/serenity-updates-sample.json';

  static async fetchAll() {
    const response = await fetch(this.jsonFilePath);

    const jsonResponse = await response.json();
    const cards = jsonResponse['data'];

    return cards.map(validateCard);
  }
}

export {CardFetcher, CardFetcherImplementation};
