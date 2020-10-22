import WidgetElement, {
  createWidget,
  EditorTypes,
  getEditorType,
  WidgetsCollection,
} from "./EditorHighlight/WidgetElement";
import EditorElement, {
  convertHtmlContent,
  getEditorContent,
  isTextArea,
  isTextInput,
  setEditorContent,
} from "./EditorHighlight/EditorElement";
import HighlightElement, {
  createHighlight,
  HighlightsCollection,
  isIntersectingHighlight
} from "./EditorHighlight/HighlightElement";
import MistakeElement from "./EditorHighlight/MistakeElement";

export * from './getQualityBadgeLevel';

export {
  WidgetElement, createWidget, WidgetsCollection, EditorTypes, getEditorType,
  EditorElement, getEditorContent, setEditorContent, isTextInput, isTextArea, convertHtmlContent,
  HighlightElement, createHighlight, HighlightsCollection, isIntersectingHighlight,
  MistakeElement
};
