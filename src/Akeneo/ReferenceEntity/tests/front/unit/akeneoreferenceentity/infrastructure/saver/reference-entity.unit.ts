'use strict';

import saver from 'akeneoreferenceentity/infrastructure/saver/reference-entity';
import {createReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {createReferenceEntityCreation} from 'akeneoreferenceentity/domain/model/reference-entity/creation';
import {createCode} from 'akeneoreferenceentity/domain/model/code';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createIdentifier as createAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import Image from 'akeneoreferenceentity/domain/model/file';
import * as fetch from 'akeneoreferenceentity/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('Akeneoreferenceentity > infrastructure > saver > reference-entity', () => {
  it('It saves a reference entity', async () => {
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const savedSofa = createReferenceEntity(
      createIdentifier('sofa'),
      createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'}),
      Image.createEmpty(),
      createAttributeIdentifier(''),
      createAttributeIdentifier('')
    );
    const response = await saver.save(savedSofa);

    expect(response).toEqual(undefined);
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_reference_entities_reference_entity_edit_rest', {
      attribute_as_image: '',
      attribute_as_label: '',
      code: 'sofa',
      identifier: 'sofa',
      image: null,
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
    });
  });

  it('It creates a reference entity', async () => {
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const sofaCreated = createReferenceEntityCreation(
      createCode('sofa'),
      createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'})
    );
    const response = await saver.create(sofaCreated);

    expect(response).toEqual(undefined);
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_reference_entities_reference_entity_create_rest', {
      code: 'sofa',
      labels: {en_US: 'Sofa', fr_FR: 'Canapé'},
    });
  });

  it('It returns errors when we create an invalid reference entity', async () => {
    const errors = [
      {
        messageTemplate: 'This value should not be blank.',
        parameters: {
          '{{ value }}': '',
        },
        plural: null,
        message: 'This value should not be blank.',
        root: {
          identifier: '',
          labels: {
            en_US: 'deefef',
          },
        },
        propertyPath: 'identifier',
        invalidValue: '',
        constraint: {
          defaultOption: null,
          requiredOptions: [],
          targets: 'property',
          payload: null,
        },
        cause: null,
        code: null,
      },
    ];

    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve(errors));

    const sofaCreated = createReferenceEntityCreation(
      createCode('sofa'),
      createLabelCollection({en_US: 'Sofa', fr_FR: 'Canapé'})
    );
    const response = await saver.create(sofaCreated);

    expect(response).toEqual(errors);
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_reference_entities_reference_entity_create_rest', {
      code: 'sofa',
      labels: {
        en_US: 'Sofa',
        fr_FR: 'Canapé',
      },
    });
  });
});
