import reducer, {
  FamilyMappingState
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping/family-mapping';
import {ATTRIBUTE_ADDED_TO_FAMILY} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/add-attribute-to-family';
import {ATTRIBUTE_CREATED} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/create-attribute';
import {
  FETCHED_FAMILY_MAPPING_FAIL,
  FETCHED_FAMILY_MAPPING_SUCCESS,
  SELECT_FAMILY,
  setLoadFamilyMapping
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family-mapping';
import {AttributeMappingStatus} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {AttributesMapping} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attributes-mapping';
import {FranklinAttributeType} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';
import {
  SAVED_FAMILY_MAPPING_SUCCESS,
  SAVED_FAMILY_MAPPING_FAIL
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/save-family-mapping';
import {FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/deactivate-franklin-attribute-mapping';
import {MAP_FRANKLIN_ATTRIBUTE} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/action/map-franklin-attribute';
import {UNMAP_FRANKLIN_ATTRIBUTE} from '../../../../../../Infrastructure/Symfony/Resources/public/react/domain/action/unmap-franklin-attribute';

let defaultMapping = {};

describe('Application > Reducer > Family Mapping > Family Mapping', () => {
  beforeEach(() => {
    defaultMapping = {
      ['connector_type(s)']: {
        franklinAttribute: {
          code: 'connector_type(s)',
          label: '',
          type: FranklinAttributeType.TEXT,
          summary: []
        },
        attribute: null,
        canCreateAttribute: true,
        status: AttributeMappingStatus.PENDING,
        exactMatchAttributeFromOtherFamily: null
      }
    };
  });

  it('should return the initial state', () => {
    const expectedState: FamilyMappingState = {
      mapping: {},
      originalMapping: {},
      loadFamilyMapping: false
    };
    expect(reducer(undefined, {} as any)).toEqual(expectedState);
  });

  it('should handle ATTRIBUTE_CREATED', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'connector_type_s_',
          canCreateAttribute: false,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };
    expect(
      reducer(initialState, {
        type: ATTRIBUTE_CREATED,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type(s)',
        attributeCode: 'connector_type_s_'
      })
    ).toEqual(expectedState);
  });

  it('should handle ATTRIBUTE_ADDED_TO_FAMILY', () => {
    defaultMapping['connector_type(s)'].exactMatchAttributeFromOtherFamily = 'connector_type';
    defaultMapping['connector_type(s)'].canCreateAttribute = false;
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'connector_type',
          canCreateAttribute: false,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };
    expect(
      reducer(initialState, {
        type: ATTRIBUTE_ADDED_TO_FAMILY,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type(s)',
        attributeCode: 'connector_type'
      })
    ).toEqual(expectedState);
  });

  it('should handle FETCHED_FAMILY_MAPPING_SUCCESS', () => {
    const initialState: FamilyMappingState = {
      mapping: {},
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'connector_type_s_',
          canCreateAttribute: false,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null,
          suggestions: []
        }
      },
      originalMapping: {
        ['connector_type(s)']: {
          attribute: 'connector_type_s_',
          status: AttributeMappingStatus.ACTIVE
        }
      },
      loadFamilyMapping: false
    };

    const mapping = {
      ['connector_type(s)']: {
        franklinAttribute: {
          code: 'connector_type(s)',
          label: '',
          type: FranklinAttributeType.TEXT,
          summary: []
        },
        attribute: 'connector_type_s_',
        canCreateAttribute: false,
        status: AttributeMappingStatus.ACTIVE,
        exactMatchAttributeFromOtherFamily: null,
        suggestions: []
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

  it('should handle SAVED_FAMILY_MAPPING_SUCCESS', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: null,
          canCreateAttribute: true,
          status: AttributeMappingStatus.PENDING,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {
        ['connector_type(s)']: {
          attribute: null,
          status: AttributeMappingStatus.PENDING
        }
      },
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: SAVED_FAMILY_MAPPING_SUCCESS
      })
    ).toEqual(expectedState);
  });

  it('should handle SAVED_FAMILY_MAPPING_FAIL', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState = initialState;

    expect(
      reducer(initialState, {
        type: SAVED_FAMILY_MAPPING_FAIL
      })
    ).toEqual(expectedState);
  });

  it('should handle SELECT_FAMILY', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      familyCode: 'headphones',
      mapping: {},
      originalMapping: {},
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: SELECT_FAMILY,
        familyCode: 'headphones'
      })
    ).toEqual(expectedState);
  });

  it('should handle FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: null,
          canCreateAttribute: true,
          status: AttributeMappingStatus.INACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type(s)'
      })
    ).toEqual(expectedState);
  });

  it('should handle MAP_FRANKLIN_ATTRIBUTE', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'connector_type_s_',
          canCreateAttribute: true,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: MAP_FRANKLIN_ATTRIBUTE,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type(s)',
        attributeCode: 'connector_type_s_'
      })
    ).toEqual(expectedState);
  });

  it('should handle APPLY_FRANKLIN_SUGGESTION', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'connector_type_s_',
          canCreateAttribute: true,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: MAP_FRANKLIN_ATTRIBUTE,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type(s)',
        attributeCode: 'connector_type_s_'
      })
    ).toEqual(expectedState);
  });

  it('should handle UNMAP_FRANKLIN_ATTRIBUTE', () => {
    const initialState: FamilyMappingState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'connector_type_s_',
          canCreateAttribute: true,
          status: AttributeMappingStatus.PENDING,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: null,
          canCreateAttribute: true,
          status: AttributeMappingStatus.PENDING,
          exactMatchAttributeFromOtherFamily: null
        }
      },
      originalMapping: {},
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: UNMAP_FRANKLIN_ATTRIBUTE,
        familyCode: 'headphones',
        franklinAttributeCode: 'connector_type(s)'
      })
    ).toEqual(expectedState);
  });

  it('should handle FETCHED_FAMILY_MAPPING_FAIL', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      mapping: {},
      originalMapping: {},
      loadFamilyMapping: false
    };

    expect(
      reducer(initialState, {
        type: FETCHED_FAMILY_MAPPING_FAIL
      })
    ).toEqual(expectedState);
  });

  it('should handle SET_LOAD_FAMILY_MAPPING', () => {
    const initialState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: false
    };
    const expectedState: FamilyMappingState = {
      mapping: defaultMapping,
      originalMapping: {},
      loadFamilyMapping: true
    };

    expect(reducer(initialState, setLoadFamilyMapping(true))).toEqual(expectedState);
  });
});
