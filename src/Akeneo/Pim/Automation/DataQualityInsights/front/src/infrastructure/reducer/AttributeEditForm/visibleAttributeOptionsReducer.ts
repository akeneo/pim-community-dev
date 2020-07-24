import {Reducer} from 'react';

export const ADD_VISIBLE_ATTRIBUTE_OPTION_ACTION = 'attribute_edit_form/add_visible_option';
export const REMOVE_VISIBLE_ATTRIBUTE_OPTION_ACTION = 'attribute_option/remove_visible_option';

export const addVisibleAttributeOptionAction = (option: string): VisibleAttributeOptionsAction => {
  return {
    type: ADD_VISIBLE_ATTRIBUTE_OPTION_ACTION,
    option
  }
}
export const removeVisibleAttributeOptionAction = (option: string): VisibleAttributeOptionsAction => {
  return {
    type: REMOVE_VISIBLE_ATTRIBUTE_OPTION_ACTION,
    option
  }
}

export type VisibleAttributeOptions = string[];

export type VisibleAttributeOptionsAction = {
    type: string;
    option:string;
}

export const visibleAttributeOptionsReducer: Reducer<VisibleAttributeOptions, VisibleAttributeOptionsAction> = (state, action) => {
    switch (action.type) {
    case ADD_VISIBLE_ATTRIBUTE_OPTION_ACTION: {
        const {option} = action;

        if (state.indexOf(option) >= 0) {
          return state;
        }

        return [...state, option];
    }
    case REMOVE_VISIBLE_ATTRIBUTE_OPTION_ACTION: {
        const {option} = action;
        const index = state.indexOf(option);

        if (index < 0) {
          return state;
        }

        state.splice(index, 1);

        return [
          ...state
        ];
    }
    default: {
        return state;
    }
    }
};
