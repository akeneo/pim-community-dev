'use strict';

import remover from 'akeneoreferenceentity/infrastructure/remover/attribute';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import * as fetch from 'akeneoreferenceentity/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('Akeneoreferenceentity > infrastructure > remover > attribute', () => {
  it('It deletes an attribute', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const attributeIdentifierToDelete = createIdentifier('name_1234');
    const referenceEntityIdentifierToDelete = createReferenceEntityIdentifier('designer');
    await remover.remove(referenceEntityIdentifierToDelete, attributeIdentifierToDelete);

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_reference_entities_attribute_delete_rest');
  });
});
