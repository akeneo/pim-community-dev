import reducer, {
  FamilyAttributesState
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping/attributes';
import {
  FETCHED_FAMILY_ATTRIBUTES_FAIL,
  FETCHED_FAMILY_ATTRIBUTES_SUCCESS,
  setLoadAttributes
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family-attributes';
import {AttributeType} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-type.enum';

it('should return the initial state', () => {
  const expectedState: FamilyAttributesState = {
    attributes: {},
    loadAttributes: false
  };
  expect(reducer(undefined, {} as any)).toEqual(expectedState);
});

it('should handle FAMILY_ATTRIBUTES_FETCHED', () => {
  const initialState: FamilyAttributesState = {
    attributes: {
      connector_type_s_: {
        code: 'connector_type_s_',
        type: AttributeType.TEXT,
        labels: {},
        group: ''
      }
    },
    loadAttributes: false
  };
  const expectedState: FamilyAttributesState = {
    attributes: {
      color: {
        code: 'color',
        type: AttributeType.TEXT,
        labels: {},
        group: ''
      }
    },
    loadAttributes: false
  };
  expect(
    reducer(initialState, {
      type: FETCHED_FAMILY_ATTRIBUTES_SUCCESS,
      attributes: {
        color: {code: 'color', type: AttributeType.TEXT, labels: {}, group: ''}
      }
    })
  ).toEqual(expectedState);
});

it('should handle FAMILY_ATTRIBUTES_FETCH_FAILED', () => {
  const initialState: FamilyAttributesState = {
    attributes: {},
    loadAttributes: false
  };
  const expectedState = {
    attributes: {},
    loadAttributes: false
  };

  expect(
    reducer(initialState, {
      type: FETCHED_FAMILY_ATTRIBUTES_FAIL
    })
  ).toEqual(expectedState);
});

it('should handle SET_LOAD_ATTRIBUTES', () => {
  const initialState: FamilyAttributesState = {
    attributes: {},
    loadAttributes: false
  };
  const expectedState = {
    attributes: {},
    loadAttributes: true
  };

  expect(reducer(initialState, setLoadAttributes(true))).toEqual(expectedState);
});
