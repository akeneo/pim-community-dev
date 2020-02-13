'use strict';

import saver from 'akeneoreferenceentity/infrastructure/saver/record';
import {createRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';
import Image from 'akeneoreferenceentity/domain/model/file';
import * as fetch from 'akeneoreferenceentity/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('Akeneoreferenceentity > infrastructure > saver > record', () => {
  it('It creates a record', async () => {
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    const recordCreated = createRecord(
      createIdentifier('designer_starck_1'),
      createReferenceEntityIdentifier('designer'),
      createRecordCode('starck'),
      createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'}),
      Image.createEmpty(),
      createValueCollection([])
    );
    const response = await saver.create(recordCreated);

    expect(response).toEqual(undefined);
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_reference_entities_record_create_rest', {
      code: 'starck',
      identifier: 'designer_starck_1',
      image: null,
      labels: {en_US: 'Stylist', fr_FR: 'Styliste'},
      reference_entity_identifier: 'designer',
      values: [],
    });
  });

  it('It returns errors when we create an invalid record', async () => {
    const errors = [
      {
        messageTemplate: 'This field may only contain letters, numbers and underscores.',
        parameters: {
          '{{ value }}': '/',
        },
        plural: null,
        message: 'pim_reference_entity.record.validation.identifier.pattern',
        root: {
          identifier: 'invalid/identifier',
          labels: {
            en_US: 'Stylist',
            fr_FR: 'Styliste',
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

    const recordCreated = createRecord(
      createIdentifier('invalid/identifier'),
      createReferenceEntityIdentifier('designer'),
      createRecordCode('invalid/identifier'),
      createLabelCollection({en_US: 'Stylist', fr_FR: 'Styliste'}),
      Image.createEmpty(),
      createValueCollection([])
    );
    const response = await saver.create(recordCreated);

    expect(response).toEqual(errors);
    expect(fetch.postJSON).toHaveBeenCalledWith('akeneo_reference_entities_record_create_rest', {
      code: 'invalid/identifier',
      identifier: 'invalid/identifier',
      image: null,
      labels: {en_US: 'Stylist', fr_FR: 'Styliste'},
      reference_entity_identifier: 'designer',
      values: [],
    });
  });
});
