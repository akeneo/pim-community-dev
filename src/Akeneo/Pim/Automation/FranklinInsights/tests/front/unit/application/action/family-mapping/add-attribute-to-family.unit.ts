import {
  addAttributeToFamily,
  attributeAddedToFamily
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/add-attribute-to-family';
import {NotificationLevel} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/notification-level';

jest.mock(
  '../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/add-attribute-to-family'
);
import {notify} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/notify';
import {addAttributeToFamily as addAttributeToFamilySaver} from '../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/add-attribute-to-family';

jest.mock('../../../../../../Infrastructure/Symfony/Resources/public/react/application/get-family-label');
import {getFamilyLabel} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/get-family-label';

const franklinAttributeCode = 'connector_type(s)';
const familyCode = 'headphones';
const pimAttributeCode = 'connector_type_s_';

it('adds an attribute to a family', async () => {
  const dispatch = jest.fn();
  const getState = jest.fn();

  getState.mockReturnValue({
    family: {
      headphones: {
        familyCode: 'headphones',
        labels: {
          en_US: 'Headphones'
        }
      }
    }
  });

  addAttributeToFamilySaver.mockResolvedValue({pimAttributeCode});

  const promise = addAttributeToFamily(familyCode, franklinAttributeCode, pimAttributeCode);

  expect(typeof promise).toBe('function');

  await promise(dispatch, getState);

  expect(getFamilyLabel).toHaveBeenCalled();

  expect(dispatch).toHaveBeenCalledWith(attributeAddedToFamily(familyCode, franklinAttributeCode, pimAttributeCode));

  expect(dispatch).toHaveBeenCalledWith(
    notify(
      NotificationLevel.SUCCESS,
      'akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_success'
    )
  );
});

it('fails to add an attribute to a family', async () => {
  const dispatch = jest.fn();

  addAttributeToFamilySaver.mockReturnValue(Promise.reject());
  const promise = addAttributeToFamily(familyCode, franklinAttributeCode, pimAttributeCode);

  expect(typeof promise).toBe('function');

  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith(
    notify(
      NotificationLevel.ERROR,
      'akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_error'
    )
  );
});
