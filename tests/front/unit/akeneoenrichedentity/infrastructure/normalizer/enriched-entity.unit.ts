import enrichedEntityNormalizer from 'akeneoenrichedentity/infrastructure/normalizer/enriched-entity';
import {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

const designerIdentifier = createIdentifier('designer');
const designerLabels = createLabelCollection({en_US: 'Designer'});
const designerEnrichedEntity = createEnrichedEntity(designerIdentifier, designerLabels);

describe('akeneo > enriched entity > infrastructue > normalizer --- enriched-entity', () => {
  test('I can normalize an enriched entity', () => {

    expect(enrichedEntityNormalizer.normalize(designerEnrichedEntity))
      .toEqual({
        identifier: designerIdentifier.stringValue(),
        labels: designerLabels.getLabels()
      });
  });
});
