import {RefObject} from 'react';
import {Action, ActionCreator, Reducer} from 'redux';
import {HighlightElement, WidgetElement} from "../../domain";
import MistakeElement from "../../domain/Spellcheck/MistakeElement";

export type ProductSpellcheckAction = WidgetAction & WidgetElementsAction & PopoverAction;

export interface ProductSpellcheckState {
  widgets: WidgetsState;
  popover: PopoverState;
}

type WidgetsState = WidgetsCollection;

export interface WidgetsCollection {
  [id: string]: WidgetElement;
}

enum PopoverPlacement {
  BOTTOM = "bottom",
  TOP = "top",
}

interface PopoverState {
  isOpen: boolean;
  placement: PopoverPlacement;
  mistake: MistakeElement | null;
  highlightRef: RefObject<Element> | null;
  handleOpening: Function;
  handleClosing: Function;
}

export interface WidgetAction extends Action {
  payload: {
    widget: {
      id: string;
      content?: string;
      analysis?: MistakeElement[],
      highlights?: HighlightElement[];
      options?: object[];
    };
  }
}

interface WidgetElementsAction extends Action {
  payload: {
    widgets: {
      [id: string]: WidgetElement;
    };
  }
}

interface PopoverAction extends Action {
  payload: {
    popover: {
      isOpen?: boolean;
      mistake?: MistakeElement|null;
      highlightRef?: RefObject<Element>|null;
      handleOpening?(mistake: MistakeElement, highlightRef: RefObject<Element>, callback: Function): void;
      handleClosing?(callback: Function): void;
    }
  }
}

const INITIALIZE_WIDGETS_LIST = "INITIALIZE_WIDGETS_LIST";
export const initializeWidgetsListAction = (widgets: WidgetsCollection) => {
  return {
    type: INITIALIZE_WIDGETS_LIST,
    payload: {
      widgets
    }
  };
};

const SHOW_WIDGET = "SHOW_WIDGET";
export const showWidgetAction = (id: string) => {
  return {
    type: SHOW_WIDGET,
    payload: {
      widget: {
        id
      }
    }
  };
};

const ENABLE_WIDGET = "ENABLE_WIDGET";
export const enableWidgetAction = (id: string) => {
  return {
    type: ENABLE_WIDGET,
    payload: {
      widget: {
        id
      }
    }
  };
};

const DISABLE_WIDGET = "DISABLE_WIDGET";
export const disableWidgetAction = (id: string) => {
  return {
    type: DISABLE_WIDGET,
    payload: {
      widget: {
        id
      }
    }
  };
};

const UPDATE_WIDGET_CONTENT = "UPDATE_WIDGET_CONTENT";
export const updateWidgetContent = (id: string, content: string) => {
  return {
    type: UPDATE_WIDGET_CONTENT,
    payload: {
      widget: {
        id,
        content
      }
    }
  };
};

const UPDATE_WIDGET_CONTENT_ANALYSIS = "UPDATE_WIDGET_CONTENT_ANALYSIS";
export const updateWidgetContentAnalysis = (id: string, analysis: MistakeElement[]) => {
  return {
    type: UPDATE_WIDGET_CONTENT_ANALYSIS,
    payload: {
      widget: {
        id,
        analysis
      }
    }
  };
};

const UPDATE_WIDGET_HIGHLIGHTS = "UPDATE_WIDGET_HIGHLIGHTS";
export const updateWidgetHighlightsAction = (id: string, highlights: HighlightElement[]) => {
  return {
    type: UPDATE_WIDGET_HIGHLIGHTS,
    payload: {
      widget: {
        id,
        highlights
      }
    }
  };
};

const UPDATE_WIDGET_EDITOR_OPTIONS = "UPDATE_WIDGET_EDITOR_OPTIONS";
export const updateWidgetEditorOptionsAction = (id: string, options: object) => {
  return {
    type: UPDATE_WIDGET_EDITOR_OPTIONS,
    payload: {
      widget: {
        id,
        options
      }
    }
  };
};

const SHOW_POPOVER = 'SHOW_POPOVER';
export const showPopoverAction: ActionCreator<PopoverAction> = (mistake: MistakeElement, highlightRef: RefObject<Element>) => {
  return {
    type: SHOW_POPOVER,
    payload: {
      popover: {
        mistake,
        highlightRef
      }
    }
  }
};
const HIDE_POPOVER = 'HIDE_POPOVER';
export const hidePopoverAction: ActionCreator<PopoverAction> = () => {
  return {
    type: HIDE_POPOVER,
    payload: {
      popover: {}
    }
  }
};
const INITIALIZE_POPOVER_OPENING = 'INITIALIZE_POPOVER_OPENING';
export const initializePopoverOpeningAction: ActionCreator<PopoverAction> = (handleOpening, handleClosing) => {
  return {
    type: INITIALIZE_POPOVER_OPENING,
    payload: {
      popover: {
        handleOpening,
        handleClosing
      }
    }
  }
};

const initialPopoverState = {
  isOpen: false,
  placement: PopoverPlacement.BOTTOM,
  mistake: null,
  highlightRef: null,
  handleOpening: () => {},
  handleClosing: () => {},
};
const initialState: ProductSpellcheckState = {
  widgets: {},
  popover: initialPopoverState
};

const widgetsReducer: Reducer<WidgetsState, WidgetElementsAction & WidgetAction> = (previousState = {}, {type, payload}) => {
  switch(type) {
    case INITIALIZE_WIDGETS_LIST:
      const {widgets} = payload;
      return {
        ...previousState,
        ...widgets
      };

    case SHOW_WIDGET: {
      const {widget} = payload;

      if (!previousState[widget.id]) {
        return previousState;
      }
      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          isVisible: true
        }
      };
    }

    case ENABLE_WIDGET: {
      const {widget} = payload;

      if (!previousState[widget.id]) {
        return previousState;
      }
      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          isActive: true
        }
      };
    }

    case DISABLE_WIDGET: {
      const {widget} = payload;

      if (!previousState[widget.id]) {
        return previousState;
      }
      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          isActive: false
        }
      };
    }

    case UPDATE_WIDGET_CONTENT: {
      const {widget} = payload;

      if (!previousState[widget.id]) {
        return previousState;
      }
      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          content: widget.content || ''
        }
      };
    }

    case UPDATE_WIDGET_CONTENT_ANALYSIS: {
      const {widget} = payload;

      if (!previousState[widget.id]) {
        return previousState;
      }
      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          analysis: widget.analysis || []
        }
      };
    }

    case UPDATE_WIDGET_HIGHLIGHTS: {
      const {widget} = payload;

      if (!previousState[widget.id]) {
        return previousState;
      }

      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          highlights: widget.highlights || []
        }
      };
    }
    default:
      return previousState;
  }
};

const popoverReducer: Reducer<PopoverState, PopoverAction> = (previousState =  initialPopoverState, {type, payload}) => {
  switch (type) {
    case SHOW_POPOVER: {
      const {popover} = payload;
      return {
        ...previousState,
        isOpen: true,
        mistake: popover.mistake || null,
        highlightRef: popover.highlightRef || null,
      };
    }
    case HIDE_POPOVER:
      return {
        ...previousState,
        isOpen: false,
        mistake: null,
        highlightRef: null,
      };
    case INITIALIZE_POPOVER_OPENING: {
      const {popover} = payload;
      return {
        ...previousState,
        handleOpening: popover.handleOpening || previousState.handleOpening,
        handleClosing: popover.handleClosing || previousState.handleClosing,
      };
    }
    default:
      return previousState;
  }
};

const productSpellcheckReducer: Reducer<ProductSpellcheckState, ProductSpellcheckAction> = (previousState = initialState, {type, payload}) => {
  return {
    widgets: widgetsReducer(previousState.widgets, {type, payload}),
    popover: popoverReducer(previousState.popover, {type, payload}),
  }
};
export default productSpellcheckReducer;
