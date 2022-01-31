'use strict';

import remover from 'akeneoreferenceentity/infrastructure/remover/record';
import {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import * as fetch from 'akeneoreferenceentity/tools/fetch';

jest.mock('routing', () => ({
  generate: (url: string) => url,
}));

describe('Akeneoreferenceentity > infrastructure > remover > record', () => {
  it('It deletes a record', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const recordCodeToDelete = createRecordCode('starck');
    const referenceEntityIdentifier = createReferenceEntityIdentifier('designer');
    await remover.remove(referenceEntityIdentifier, recordCodeToDelete);

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_reference_entities_record_delete_rest');
  });

  it('It deletes all reference entity records', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const referenceEntityIdentifier = createReferenceEntityIdentifier('designer');
    await remover.removeAll(referenceEntityIdentifier);

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_reference_entities_record_delete_all_rest');
  });
});
