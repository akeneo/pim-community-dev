import Locale from 'akeneoreferenceentity/domain/model/locale';

export default interface Fetcher {
  fetchActivated: () => Promise<Locale[]>;
}
