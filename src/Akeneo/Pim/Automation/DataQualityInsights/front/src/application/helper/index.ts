import WidgetElement, {createWidget, WidgetsCollection} from "./EditorHighlight/WidgetElement";
import EditorElement, {
  getEditorContent,
  isTextArea,
  isTextInput,
  setEditorContent
} from "./EditorHighlight/EditorElement";
import HighlightElement, {
  createHighlight,
  HighlightsCollection,
  isIntersectingHighlight
} from "./EditorHighlight/HighlightElement";
import MistakeElement from "./EditorHighlight/MistakeElement";

export {
  WidgetElement, createWidget, WidgetsCollection,
  EditorElement, getEditorContent, setEditorContent, isTextInput, isTextArea,
  HighlightElement, createHighlight, HighlightsCollection, isIntersectingHighlight,
  MistakeElement
};
