import {
  attributeCreated,
  createAttribute
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/create-attribute';
import {notify} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/notify';
import {NotificationLevel} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/notification-level';

jest.mock('../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/create-attribute');
import {createAttribute as create} from '../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/create-attribute';

jest.mock('../../../../../../Infrastructure/Symfony/Resources/public/react/application/get-family-label');
import {getFamilyLabel} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/get-family-label';

const familyCode = 'headphones';
const franklinAttributeCode = 'connector_type(s)';
const franklinAttributeType = 'text';
const franklinAttributeLabel = 'Connector Type(s)';
const attributeCode = 'connector_type_s_';

it('creates an attribute', async () => {
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

  create.mockReturnValue(Promise.resolve({attributeCode}));

  const promise = createAttribute(familyCode, franklinAttributeCode, franklinAttributeType, franklinAttributeLabel);
  expect(typeof promise).toBe('function');

  await promise(dispatch, getState);

  expect(getFamilyLabel).toHaveBeenCalled();

  expect(dispatch).toHaveBeenCalledWith(attributeCreated(familyCode, franklinAttributeCode, attributeCode));

  expect(dispatch).toHaveBeenCalledWith(
    notify(
      NotificationLevel.SUCCESS,
      'akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_success'
    )
  );
});

it('fails to create an attribute', async () => {
  const dispatch = jest.fn();
  create.mockReturnValue(Promise.reject());

  const promise = createAttribute(familyCode, franklinAttributeCode, franklinAttributeType, franklinAttributeLabel);
  expect(typeof promise).toBe('function');

  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith(
    notify(NotificationLevel.ERROR, 'akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_error')
  );
});
