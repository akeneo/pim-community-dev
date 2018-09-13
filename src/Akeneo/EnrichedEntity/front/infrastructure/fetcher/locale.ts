import LocaleFetcher from 'akeneoenrichedentity/domain/fetcher/locale';
import Locale from 'akeneoenrichedentity/domain/model/locale';
import hydrator from 'akeneoenrichedentity/application/hydrator/locale';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

const routing = require('routing');

export class LocaleFetcherImplementation implements LocaleFetcher {
  constructor(private hydrator: (backendLocale: any) => Locale) {
    Object.freeze(this);
  }

  async fetchActivated(): Promise<Locale[]> {
    const backendLocales = await getJSON(routing.generate('pim_enrich_locale_rest_index'), {activated: true}).catch(
      errorHandler
    );

    return hydrateAll<Locale>(this.hydrator)(backendLocales);
  }
}

export default new LocaleFetcherImplementation(hydrator);
