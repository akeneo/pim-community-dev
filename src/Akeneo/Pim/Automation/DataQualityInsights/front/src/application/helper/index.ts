import WidgetElement, {
  createWidget,
  EditorTypes,
  getEditorType,
  WidgetsCollection,
} from './EditorHighlight/WidgetElement';
import EditorElement, {
  getEditorContent,
  isTextArea,
  isTextInput,
  setEditorContent,
  convertHtmlContent,
} from './EditorHighlight/EditorElement';
import HighlightElement, {
  createHighlight,
  HighlightsCollection,
  isIntersectingHighlight,
} from './EditorHighlight/HighlightElement';
import MistakeElement from './EditorHighlight/MistakeElement';

export * from './getQualityBadgeLevel';

export {
  WidgetElement,
  createWidget,
  WidgetsCollection,
  EditorTypes,
  getEditorType,
  EditorElement,
  getEditorContent,
  setEditorContent,
  isTextInput,
  isTextArea,
  convertHtmlContent,
  HighlightElement,
  createHighlight,
  HighlightsCollection,
  isIntersectingHighlight,
  MistakeElement,
};
