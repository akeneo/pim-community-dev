import LocaleFetcher from 'akeneoreferenceentity/domain/fetcher/locale';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import hydrator from 'akeneoreferenceentity/application/hydrator/locale';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

let activatedLocales: Locale[] | null = null;
export class LocaleFetcherImplementation implements LocaleFetcher {
  async fetchActivated(): Promise<Locale[]> {
    if (null === activatedLocales) {
      const backendLocales = await getJSON(routing.generate('pim_enrich_locale_rest_index'), {activated: true}).catch(
        errorHandler
      );

      activatedLocales = hydrateAll<Locale>(hydrator)(backendLocales);
    }

    return activatedLocales;
  }
}

export default new LocaleFetcherImplementation();
