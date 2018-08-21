import AttributeRemover from 'akeneoenrichedentity/domain/remover/attribute';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';

const routing = require('routing');

export class AttributeRemoverImplementation implements AttributeRemover {
  constructor() {
    Object.freeze(this);
  }

  async remove(attributeIdentifier: AttributeIdentifier): Promise<void> {
    await deleteJSON(
      routing.generate(
        'akeneo_enriched_entities_attribute_delete_rest',
        {
          attributeIdentifier: attributeIdentifier.normalize().identifier,
          enrichedEntityIdentifier: attributeIdentifier.normalize().enrichedEntityIdentifier
        }
      )
    );
  }
}

export default new AttributeRemoverImplementation();
