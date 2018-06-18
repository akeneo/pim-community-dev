import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {RawLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

interface EnrichedEntityNormalized {
  identifier: string;
  labels: RawLabelCollection;
}

class EnrichedEntityNormalizer {
  normalize(enrichedEntity: EnrichedEntity): EnrichedEntityNormalized {
    return {
      identifier: enrichedEntity.getIdentifier().stringValue(),
      labels: enrichedEntity.getLabelCollection().getLabels(),
    };
  }
}

export default new EnrichedEntityNormalizer();
