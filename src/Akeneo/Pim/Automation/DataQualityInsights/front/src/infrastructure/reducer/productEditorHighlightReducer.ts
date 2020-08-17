import {Action, ActionCreator, Reducer} from 'redux';
import {
  HighlightElement,
  HighlightsCollection,
  MistakeElement,
  WidgetElement,
  WidgetsCollection
} from "../../application/helper";

export type ProductHighlightAction = WidgetAction & WidgetElementsAction & PopoverAction;

export interface ProductEditorHighlightState {
  widgets: WidgetsState;
  popover: PopoverState;
}

type WidgetsState = WidgetsCollection;

interface PopoverState {
  isOpen: boolean;
  highlight: HighlightElement | null;
  widgetId: string | null;
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
      highlightId?: string;
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
      highlight?: HighlightElement|null;
      widgetId?: string | null;
      handleOpening?(widgetId: string, highlight: HighlightElement, callback: Function): void;
      handleClosing?(callback: Function): void;
    }
  }
}

const INITIALIZE_WIDGETS_LIST = "INITIALIZE_WIDGETS_LIST";
export const initializeWidgetsListAction: ActionCreator<WidgetElementsAction> = (widgets: WidgetsCollection) => {
  return {
    type: INITIALIZE_WIDGETS_LIST,
    payload: {
      widgets
    }
  };
};

const SHOW_WIDGET = "SHOW_WIDGET";
export const showWidgetAction: ActionCreator<WidgetAction> = (id: string) => {
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
export const enableWidgetAction: ActionCreator<WidgetAction> = (id: string) => {
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
export const disableWidgetAction: ActionCreator<WidgetAction> = (id: string) => {
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
export const updateWidgetContent: ActionCreator<WidgetAction> = (id: string, content: string) => {
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
export const updateWidgetContentAnalysis: ActionCreator<WidgetAction> = (id: string, analysis: MistakeElement[]) => {
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
export const updateWidgetHighlightsAction: ActionCreator<WidgetAction> = (id: string, highlights: HighlightElement[]) => {
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

const ENABLE_WIDGET_HIGHLIGHT = 'ENABLE_WIDGET_HIGHLIGHT';
export const enableWidgetHighlightAction: ActionCreator<WidgetAction> = (id: string, highlightId: string) => {
  return {
    type: ENABLE_WIDGET_HIGHLIGHT,
    payload: {
      widget: {
        id,
        highlightId
      }
    }
  };
};
const DISABLE_WIDGET_HIGHLIGHT = 'DISABLE_WIDGET_HIGHLIGHT';
export const disableWidgetHighlightAction: ActionCreator<WidgetElementsAction> = () => {
  return {
    type: DISABLE_WIDGET_HIGHLIGHT,
    payload: {
      widgets: {}
    }
  };
};
const SHOW_POPOVER = 'SHOW_POPOVER';
export const showPopoverAction: ActionCreator<PopoverAction> = (widgetId: string, highlight: HighlightElement) => {
  return {
    type: SHOW_POPOVER,
    payload: {
      popover: {
        widgetId,
        highlight
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
  highlight: null,
  widgetId: null,
  handleOpening: () => {},
  handleClosing: () => {},
};
const initialState: ProductEditorHighlightState = {
  widgets: {},
  popover: initialPopoverState,
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

      const highlights = widget.highlights || [];
      const highlightsCollection: HighlightsCollection = {};

      highlights.forEach((highlight: HighlightElement) => {
        highlightsCollection[highlight.id] = highlight;
      });

      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          highlights: highlightsCollection
        }
      };
    }

    case ENABLE_WIDGET_HIGHLIGHT: {
      const {widget} = payload;

      if (!previousState[widget.id] || !widget.highlightId || !previousState[widget.id].highlights[widget.highlightId]) {
        return previousState;
      }

      const previousHighlights = previousState[widget.id].highlights;
      const highlightsCollection:HighlightsCollection = {};

      Object.values(previousHighlights).forEach((highlight: HighlightElement) => {
        highlightsCollection[highlight.id] = {
          ...highlight,
          isActive: (highlight.id === widget.highlightId)
        }
      });

      return {
        ...previousState,
        [widget.id]: {
          ...previousState[widget.id],
          highlights: highlightsCollection,
        }
      }
    }

    case DISABLE_WIDGET_HIGHLIGHT: {
      const state: WidgetsState = {};

      Object.values(previousState).forEach((widget: WidgetElement) => {
        const highlights: HighlightsCollection = {};

        Object.values(widget.highlights).forEach((highlight: HighlightElement) => {
          highlights[highlight.id] = {
            ...highlight,
            isActive: false
          };
        });

        state[widget.id] = {
          ...widget,
          highlights
        };
      });

      return state;
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
        highlight: popover.highlight || null,
        widgetId: popover.widgetId || null,
      };
    }
    case HIDE_POPOVER:
      return {
        ...previousState,
        isOpen: false,
        highlight: null,
        widgetId: null,
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

const productEditorHighlightReducer: Reducer<ProductEditorHighlightState, ProductHighlightAction> = (previousState = initialState, {type, payload}) => {
  return {
    widgets: widgetsReducer(previousState.widgets, {type, payload}),
    popover: popoverReducer(previousState.popover, {type, payload}),
  }
};
export default productEditorHighlightReducer;
