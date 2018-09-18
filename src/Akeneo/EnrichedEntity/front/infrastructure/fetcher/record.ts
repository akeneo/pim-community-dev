import RecordFetcher from 'akeneoenrichedentity/domain/fetcher/record';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import hydrator from 'akeneoenrichedentity/application/hydrator/record';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';
import attributeFetcher from 'akeneoenrichedentity/infrastructure/fetcher/attribute';
import Attribute, {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';

const routing = require('routing');

export class RecordFetcherImplementation implements RecordFetcher {
  constructor(private hydrator: (backendRecord: any) => Record) {
    Object.freeze(this);
  }

  async fetch(enrichedEntityIdentifier: EnrichedEntityIdentifier, recordCode: RecordCode): Promise<Record> {
    const backendRecord = await getJSON(
      routing.generate('akeneo_enriched_entities_records_get_rest', {
        enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
        recordCode: recordCode.stringValue(),
      })
    ).catch(errorHandler);

    // This is temporary as the backend does not provide attributes yet
    const attributes = (await attributeFetcher.fetchAll(enrichedEntityIdentifier)).map((attribute: Attribute) =>
      attribute.normalize()
    );

    const image = undefined === backendRecord.image ? null : backendRecord.image;

    // This is temporary as the backend does not provide values yet
    const values = [
      {
        attribute: 'image_designer_d00e1ee1-6c3d-4280-ae45-b124994491f2',
        locale: 'en_US',
        channel: null,
        data: null,
      },
      {
        attribute: 'image_designer_d00e1ee1-6c3d-4280-ae45-b124994491f2',
        locale: 'fr_FR',
        channel: null,
        data: {
          originalFilename: 'Designer.png',
          filePath: '7/5/c/f/75cf0be0ab78fa0eb550841ef26c6d8c43ed44cd_designer.jpg',
        },
      },
      {
        attribute: 'image_designer_d00e1ee1-6c3d-4280-ae45-b124994491f2',
        locale: 'de_DE',
        channel: null,
        data: {
          originalFilename: 'Designer.png',
          filePath: '7/5/c/f/75cf0be0ab78fa0eb550841ef26c6d8c43ed44cd_designer.jpg',
        },
      },
      {
        attribute: 'name_designer_16f624b3-0855-4e12-80b6-da077252a194',
        locale: 'fr_FR',
        channel: null,
        data: 'Le nom en français',
      },
      {
        attribute: 'name_designer_16f624b3-0855-4e12-80b6-da077252a194',
        locale: 'en_US',
        channel: null,
        data: 'This is his name in english',
      },
      {
        attribute: 'name_designer_16f624b3-0855-4e12-80b6-da077252a194',
        locale: 'de_DE',
        channel: null,
        data: 'Sein name auf Deutsch',
      },
      {
        attribute: 'portrait_designer_1781b92b-6785-4bdf-9837-9f0db68902d4',
        locale: null,
        channel: null,
        data: null,
      },
    ].map((value: any) => {
      return {
        ...value,
        attribute: attributes.find((attribute: NormalizedAttribute) => value.attribute === attribute.identifier),
      };
    });

    return this.hydrator({
      ...backendRecord,
      image,
      attributes,
      values,
    });
  }

  async fetchAll(enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Record[]> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {enrichedEntityIdentifier})
    ).catch(errorHandler);

    return hydrateAll<Record>(this.hydrator)(backendRecords.items);
  }

  async search(query: Query): Promise<{items: Record[]; total: number}> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {
        // This is temporary, as soon as we will have a QB in backend it will be way simpler
        enrichedEntityIdentifier: query.filters[0].value,
      })
    ).catch(errorHandler);
    const items = hydrateAll<Record>(this.hydrator)(
      backendRecords.items.map((backendRecord: any) => {
        // This is temporary: the backend should send the image and the values
        return {...backendRecord, image: undefined === backendRecord.image ? null : backendRecord.image, values: []};
      })
    );

    return {
      items,
      total: backendRecords.total,
    };
  }
}

export default new RecordFetcherImplementation(hydrator);
