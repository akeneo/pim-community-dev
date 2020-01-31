import reducer, {
  AttributesMappingStatusState
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping/attributes-mapping-status';
import {
  FETCHED_FAMILY_MAPPING_FAIL,
  FETCHED_FAMILY_MAPPING_SUCCESS
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family-mapping';
import {FranklinAttributeType} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';
import {AttributeMappingStatus} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {AttributesMapping} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attributes-mapping';
import {FrontAttributeMappingStatus} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/front-attribute-mapping-status.enum';
import {APPLY_FRANKLIN_SUGGESTION} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/action/apply-franklin-suggestion';
import {UNMAP_FRANKLIN_ATTRIBUTE} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/action/unmap-franklin-attribute';
import {MAP_FRANKLIN_ATTRIBUTE} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/action/map-franklin-attribute';
import {ATTRIBUTE_ADDED_TO_FAMILY} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/add-attribute-to-family';
import {ATTRIBUTE_CREATED} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/create-attribute';

describe('Application > Reducer > Family Mapping > Attributes mapping status', () => {
  it('should return the initial state', () => {
    const expectedState: AttributesMappingStatusState = {};
    expect(reducer(undefined, {} as any)).toEqual(expectedState);
  });

  it('should handle FETCHED_FAMILY_MAPPING_SUCCESS', () => {
    const initialState: AttributesMappingStatusState = {};
    const expectedState = {
      connector_type: FrontAttributeMappingStatus.MAPPED,
      color: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.PENDING
    };

    const mapping = {
      connector_type: {
        franklinAttribute: {
          code: 'connector_type',
          label: '',
          type: FranklinAttributeType.TEXT,
          summary: []
        },
        attribute: 'connector_type_s_',
        canCreateAttribute: false,
        status: AttributeMappingStatus.ACTIVE,
        exactMatchAttributeFromOtherFamily: null,
        suggestions: []
      },
      color: {
        franklinAttribute: {
          code: 'color',
          label: '',
          type: FranklinAttributeType.TEXT,
          summary: []
        },
        attribute: null,
        canCreateAttribute: false,
        status: AttributeMappingStatus.PENDING,
        exactMatchAttributeFromOtherFamily: null,
        suggestions: []
      },
      height: {
        franklinAttribute: {
          code: 'height',
          label: '',
          type: FranklinAttributeType.TEXT,
          summary: []
        },
        attribute: null,
        canCreateAttribute: false,
        status: AttributeMappingStatus.PENDING,
        exactMatchAttributeFromOtherFamily: null,
        suggestions: ['size', 'height']
      }
    } as AttributesMapping;

    expect(
      reducer(initialState, {
        type: FETCHED_FAMILY_MAPPING_SUCCESS,
        familyCode: 'headphones',
        mapping
      })
    ).toEqual(expectedState);
  });

  it('should handle APPLY_FRANKLIN_SUGGESTION', () => {
    const initialState = {
      connector_type: FrontAttributeMappingStatus.MAPPED,
      color: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.PENDING
    };

    const expectedState = {
      connector_type: FrontAttributeMappingStatus.MAPPED,
      color: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.SUGGESTION_APPLIED
    };

    expect(
      reducer(initialState, {
        type: APPLY_FRANKLIN_SUGGESTION,
        familyCode: 'headphones',
        franklinAttributeCode: 'height',
        pimAttributeCode: 'size'
      })
    ).toEqual(expectedState);
  });

  it('should handle FETCHED_FAMILY_MAPPING_FAIL', () => {
    const initialState = {};
    const expectedState = {};

    expect(
      reducer(initialState, {
        type: FETCHED_FAMILY_MAPPING_FAIL
      })
    ).toEqual(expectedState);
  });

  it('should handle UNMAP_FRANKLIN_ATTRIBUTE', () => {
    const initialState = {
      connector_type: FrontAttributeMappingStatus.MAPPED,
      height: FrontAttributeMappingStatus.SUGGESTION_APPLIED
    };

    const expectedState = {
      connector_type: FrontAttributeMappingStatus.MAPPED,
      height: FrontAttributeMappingStatus.PENDING
    };

    expect(
      reducer(initialState, {
        type: UNMAP_FRANKLIN_ATTRIBUTE,
        familyCode: 'headphones',
        franklinAttributeCode: 'height'
      })
    ).toEqual(expectedState);
  });

  it('should handle MAP_FRANKLIN_ATTRIBUTE', () => {
    const initialState = {
      connector_type: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.SUGGESTION_APPLIED
    };

    const expectedState = {
      connector_type: FrontAttributeMappingStatus.MAPPED,
      height: FrontAttributeMappingStatus.SUGGESTION_APPLIED
    };

    expect(
      reducer(initialState, {
        type: MAP_FRANKLIN_ATTRIBUTE,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type',
        attributeCode: 'connector'
      })
    ).toEqual(expectedState);
  });

  it('should handle ATTRIBUTE_ADDED_TO_FAMILY', () => {
    const initialState = {
      connector_type: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.PENDING
    };

    const expectedState = {
      connector_type: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.MAPPED
    };

    expect(
      reducer(initialState, {
        type: ATTRIBUTE_ADDED_TO_FAMILY,
        familyCode: 'headphones',
        franklinAttributeCode: 'height',
        attributeCode: 'pim_height'
      })
    ).toEqual(expectedState);
  });

  it('should handle ATTRIBUTE_CREATED', () => {
    const initialState = {
      connector_type: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.PENDING
    };

    const expectedState = {
      connector_type: FrontAttributeMappingStatus.PENDING,
      height: FrontAttributeMappingStatus.MAPPED
    };

    expect(
      reducer(initialState, {
        type: ATTRIBUTE_CREATED,
        familyCode: 'headphones',
        franklinAttributeCode: 'height',
        attributeCode: 'pim_height'
      })
    ).toEqual(expectedState);
  });
});
