import {HighlightsCollection} from "./HighlightElement";
import EditorElement, {getEditorContent, isEditableContent, isTextArea, isTextInput} from "./EditorElement";
import MistakeElement from "./MistakeElement";

export default interface WidgetElement {
  id: string;
  editor: EditorElement;
  editorId: string;
  attribute: string;
  content: string;
  analysis: MistakeElement[],
  highlights: HighlightsCollection,
  isVisible: boolean;
  isActive: boolean;
  isTextArea: boolean;
  isTextInput: boolean;
  isEditableContent: boolean;
}

export interface WidgetsCollection {
  [id: string]: WidgetElement;
}

export const createWidget = (identifier: string, editor: EditorElement, attribute: string) => {
  return {
    id: identifier,
    editor: editor,
    editorId: editor.id,
    attribute,
    isVisible: false,
    isActive: false,
    isTextArea: isTextArea(editor),
    isTextInput: isTextInput(editor),
    isEditableContent: isEditableContent(editor),
    content: getEditorContent(editor),
    analysis: [],
    highlights: {}
  } as WidgetElement;
};
