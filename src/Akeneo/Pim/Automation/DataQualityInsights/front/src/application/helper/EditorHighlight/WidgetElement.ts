import {HighlightsCollection} from './HighlightElement';
import EditorElement, {getEditorContent, isEditableContent, isTextArea, isTextInput} from './EditorElement';
import MistakeElement from './MistakeElement';

export default interface WidgetElement {
  id: string;
  editor: EditorElement;
  editorId: string;
  attribute: string;
  content: string;
  analysis: MistakeElement[];
  highlights: HighlightsCollection;
  isVisible: boolean;
  isActive: boolean;
  isTextArea: boolean;
  isTextInput: boolean;
  isEditableContent: boolean;
}

export interface WidgetsCollection {
  [id: string]: WidgetElement;
}

export const createWidget = (identifier: string, editor: EditorElement, editorId: string | null, attribute: string) => {
  return {
    id: identifier,
    editor: editor,
    editorId: editorId,
    attribute,
    isVisible: false,
    isActive: false,
    isTextArea: isTextArea(editor),
    isTextInput: isTextInput(editor),
    isEditableContent: isEditableContent(editor),
    content: getEditorContent(editor),
    analysis: [],
    highlights: {},
  } as WidgetElement;
};

export enum EditorTypes {
  TEXT = 'text',
  TEXTAREA = 'textarea',
  RICHTEXT = 'richtext',
  UNKNOWN = 'unknown',
}

export const getEditorType = (widget: WidgetElement) => {
  if (widget.isTextArea) {
    return EditorTypes.TEXTAREA;
  }

  if (widget.isTextInput) {
    return EditorTypes.TEXT;
  }

  if (widget.isEditableContent) {
    return EditorTypes.RICHTEXT;
  }

  return EditorTypes.UNKNOWN;
};
