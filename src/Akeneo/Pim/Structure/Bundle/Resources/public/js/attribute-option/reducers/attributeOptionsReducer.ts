import {Action, ActionCreator, Reducer} from 'redux';
import {AttributeOption} from '../model';

interface InitializeAttributeOptionsAction extends Action {
  payload: {
    attributeOptions: AttributeOption[];
  };
}

const INITIALIZE_ATTRIBUTE_OPTIONS = 'INITIALIZE_ATTRIBUTE_OPTIONS';
export const initializeAttributeOptionsAction: ActionCreator<InitializeAttributeOptionsAction> = (
  attributeOptions: AttributeOption[]
) => {
  return {
    type: INITIALIZE_ATTRIBUTE_OPTIONS,
    payload: {
      attributeOptions,
    },
  };
};

interface ResetAttributeOptionsAction extends Action {}

const RESET_ATTRIBUTE_OPTIONS = 'RESET_ATTRIBUTE_OPTIONS';
export const resetAttributeOptionsAction: ActionCreator<ResetAttributeOptionsAction> = () => {
  return {
    type: RESET_ATTRIBUTE_OPTIONS,
  };
};

interface UpdateAttributeOptionAction extends Action {
  payload: {
    option: AttributeOption;
  };
}

const UPDATE_ATTRIBUTE_OPTION = 'UPDATE_ATTRIBUTE_OPTION';
export const updateAttributeOptionAction: ActionCreator<UpdateAttributeOptionAction> = (
  attributeOption: AttributeOption
) => {
  return {
    type: UPDATE_ATTRIBUTE_OPTION,
    payload: {
      option: attributeOption,
    },
  };
};

interface CreateAttributeOptionAction extends Action {
  payload: {
    option: AttributeOption;
  };
}

const CREATE_ATTRIBUTE_OPTION = 'CREATE_ATTRIBUTE_OPTION';
export const createAttributeOptionAction: ActionCreator<CreateAttributeOptionAction> = (
  attributeOption: AttributeOption
) => {
  return {
    type: CREATE_ATTRIBUTE_OPTION,
    payload: {
      option: attributeOption,
    },
  };
};

interface DeleteAttributeOptionAction extends Action {
  payload: {
    optionId: number;
  };
}

const DELETE_ATTRIBUTE_OPTION = 'DELETE_ATTRIBUTE_OPTION';
export const deleteAttributeOptionAction: ActionCreator<DeleteAttributeOptionAction> = (optionId: number) => {
  return {
    type: DELETE_ATTRIBUTE_OPTION,
    payload: {
      optionId: optionId,
    },
  };
};

const attributeOptionsReducer: Reducer<AttributeOption[] | null> = (previousState = null, {type, payload}) => {
  switch (type) {
    case INITIALIZE_ATTRIBUTE_OPTIONS: {
      //The backend can return an empty object ({}) when there is no option and an array otherwise
      if (typeof payload.attributeOptions === 'object' && !Array.isArray(payload.attributeOptions)) {
        return [];
      }

      return [...payload.attributeOptions];
    }
    case RESET_ATTRIBUTE_OPTIONS: {
      return null;
    }
    case UPDATE_ATTRIBUTE_OPTION: {
      if (previousState === null) {
        return previousState;
      }

      const index = previousState.findIndex(
        (attributeOption: AttributeOption) => attributeOption.id === payload.option.id
      );

      let newState = [...previousState];
      newState[index] = payload.option;

      return newState;
    }
    case CREATE_ATTRIBUTE_OPTION: {
      if (previousState === null) {
        return previousState;
      }

      return [...previousState, payload.option];
    }
    case DELETE_ATTRIBUTE_OPTION: {
      if (previousState === null) {
        return previousState;
      }

      const index = previousState.findIndex(
        (attributeOption: AttributeOption) => attributeOption.id === payload.optionId
      );
      let newState = [...previousState];
      newState.splice(index, 1);

      return newState;
    }
    default:
      return previousState;
  }
};
export default attributeOptionsReducer;
