'use strict';

import remover from 'akeneoreferenceentity/infrastructure/remover/reference-entity';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import * as fetch from 'akeneoreferenceentity/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('Akeneoreferenceentity > infrastructure > remover > reference-entity', () => {
  it('It deletes a reference entity', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const identifierToDelete = createIdentifier('designer');
    await remover.remove(identifierToDelete);

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_reference_entities_reference_entity_delete_rest');
  });
});
