import Locale from 'akeneoenrichedentity/domain/model/locale';

export default interface Fetcher {
  fetchActivated: () => Promise<Locale[]>;
}
