import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';

export default interface Fetcher {
  fetchAll: (enrichedEntityIdentifier: EnrichedEntityIdentifier) => Promise<Attribute[]>;
}
