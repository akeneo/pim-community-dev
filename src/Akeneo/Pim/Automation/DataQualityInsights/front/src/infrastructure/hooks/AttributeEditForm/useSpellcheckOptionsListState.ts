import {Reducer, RefObject, useEffect, useReducer, useState} from 'react';

import {
  AttributeOptionFormEvent,
  EditAttributeOptionEvent,
  PIM_ATTRIBUTE_OPTION_EDITING,
  PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED,
  PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED,
} from 'akeneopimstructure/js/attribute-option/hooks';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {useUuid} from '../Common/useUuid';

const SPELLCHECK_ELEMENTS_UUID_NAMESPACE = 'd6982914-8568-443c-8c85-8b5b63691ad2';

export type ElementsList<P extends HTMLElement> = {
  [key: string]: {
    element: P;
    locale: string;
  };
};

export type SpellcheckOptionsListState = {
  elements: ElementsList<HTMLInputElement>;
  editingOption: AttributeOption | null;
};

type SpellcheckOptionsListAction = {
  type: string;
  id: string;
  locale: string;
  ref: RefObject<HTMLInputElement>;
};
type SpellcheckOptionsListReducer = Reducer<ElementsList<HTMLInputElement>, SpellcheckOptionsListAction>;

const reducer: SpellcheckOptionsListReducer = (state, action) => {
  switch (action.type) {
    case 'add': {
      const {locale, ref, id} = action;

      if (!ref.current) {
        return state;
      }

      return {
        ...state,
        [id]: {
          element: ref.current,
          locale,
        },
      };
    }
    case 'remove': {
      const {id} = action;

      return Object.keys(state).reduce((list, key) => {
        if (key === id) {
          return list;
        }

        return {
          ...list,
          [key]: state[key],
        };
      }, {});
    }
    default:
      return state;
  }
};

const useSpellcheckOptionsListState = (): SpellcheckOptionsListState => {
  const [elements, dispatch] = useReducer<SpellcheckOptionsListReducer>(reducer, {});
  const [editingOption, setEditingOption] = useState<AttributeOption | null>(null);
  const {uuid} = useUuid('option-element-', SPELLCHECK_ELEMENTS_UUID_NAMESPACE);

  useEffect(() => {
    const nodes = document.querySelectorAll<HTMLElement>('input[role="attribute-option-label"]');

    nodes.forEach(element => {
      const ref = {current: element as HTMLInputElement};
      const locale = element.dataset.locale;
      if (!locale) {
        return;
      }

      dispatch({type: 'add', locale, ref, id: uuid(locale)});
    });
  }, []);

  useEffect(() => {
    const handleOptionFormAdded = (event: CustomEvent<AttributeOptionFormEvent>) => {
      const {locale, ref} = event.detail;

      dispatch({type: 'add', locale, ref, id: uuid(locale)});
    };
    const handleOptionFormRemoved = (event: CustomEvent<AttributeOptionFormEvent>) => {
      const {locale, ref} = event.detail;

      dispatch({type: 'remove', locale, ref, id: uuid(locale)});
    };
    const handleEditingOption = (event: CustomEvent<EditAttributeOptionEvent>) => {
      const {option} = event.detail;
      setEditingOption(option);
    };

    window.addEventListener(PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED, handleOptionFormAdded as EventListener);
    window.addEventListener(PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED, handleOptionFormRemoved as EventListener);
    window.addEventListener(PIM_ATTRIBUTE_OPTION_EDITING, handleEditingOption as EventListener);

    return () => {
      window.removeEventListener(PIM_ATTRIBUTE_OPTION_LABEL_FORM_ADDED, handleOptionFormAdded as EventListener);
      window.removeEventListener(PIM_ATTRIBUTE_OPTION_LABEL_FORM_REMOVED, handleOptionFormRemoved as EventListener);
      window.removeEventListener(PIM_ATTRIBUTE_OPTION_EDITING, handleEditingOption as EventListener);
    };
  }, [uuid]);

  return {
    elements,
    editingOption,
  };
};

export default useSpellcheckOptionsListState;
