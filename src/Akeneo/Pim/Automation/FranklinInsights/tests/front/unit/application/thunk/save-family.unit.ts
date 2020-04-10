import {notify} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/action/notify';
import {NotificationLevel} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/notification-level';
import {saveFamilyMapping} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/thunk/save-family';
import {saveFamilyMapping as familyMappingSaver} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/family-mapping';
import {AttributeMappingStatus} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {fetchFamilyMapping} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family-mapping';
import {
  SAVED_FAMILY_MAPPING_FAIL,
  SAVED_FAMILY_MAPPING_SUCCESS
} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/save-family-mapping';

jest.mock('../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/family-mapping');
jest.mock(
  '../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family-mapping'
);

const mapping = {
  Digital_Audio_Format: {
    franklinAttribute: {
      code: 'Digital_Audio_Format',
      label: 'Digital audio format',
      type: 'text',
      summary: ['']
    },
    attribute: null,
    status: AttributeMappingStatus.PENDING,
    exactMatchAttributeFromOtherFamily: null,
    canCreateAttribute: false
  }
};

it('saves the family mapping', async () => {
  const dispatch = jest.fn();
  familyMappingSaver.mockResolvedValue({});

  const newFetchedFamilyMapping = new Promise(() => {});
  fetchFamilyMapping.mockResolvedValue(newFetchedFamilyMapping);

  const promise = saveFamilyMapping('headphones', mapping);
  expect(typeof promise).toBe('function');
  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith({
    type: SAVED_FAMILY_MAPPING_SUCCESS
  });

  expect(dispatch).toHaveBeenCalledWith(
    notify(NotificationLevel.SUCCESS, 'pim_enrich.entity.fallback.flash.update.success')
  );

  expect(dispatch).toHaveBeenCalledWith(newFetchedFamilyMapping);
});

it('fails to save the family mapping with a server error', async () => {
  const dispatch = jest.fn();
  familyMappingSaver.mockReturnValue(Promise.reject({status: 500}));

  const promise = saveFamilyMapping('headphones', mapping);
  expect(typeof promise).toBe('function');
  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith({
    type: SAVED_FAMILY_MAPPING_FAIL
  });

  expect(dispatch).toHaveBeenCalledWith(
    notify(NotificationLevel.ERROR, 'pim_enrich.entity.fallback.flash.update.fail')
  );
});

it('fails to save the family mapping due to a bad request', async () => {
  const dispatch = jest.fn();
  familyMappingSaver.mockReturnValue(
    Promise.reject({
      status: 400,
      responseJSON: ['akeneo_franklin_insights.entity.attributes_mapping.constraint.invalid_attribute_type_mapping']
    })
  );

  const promise = saveFamilyMapping('headphones', mapping);
  expect(typeof promise).toBe('function');
  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith({
    type: SAVED_FAMILY_MAPPING_FAIL
  });

  expect(dispatch).toHaveBeenCalledWith(
    notify(
      NotificationLevel.ERROR,
      'akeneo_franklin_insights.entity.attributes_mapping.constraint.invalid_attribute_type_mapping'
    )
  );
});
