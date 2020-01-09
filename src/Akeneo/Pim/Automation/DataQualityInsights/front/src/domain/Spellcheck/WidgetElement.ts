import HighlightElement from "./HighlightElement";
import EditorElement, {getEditorContent, isEditableContent, isTextArea, isTextInput} from "./EditorElement";
import MistakeElement from "./MistakeElement";

export default interface WidgetElement {
  id: string;
  editor: EditorElement;
  editorId: string;
  attribute: string;
  shadowEditor: EditorElement | null;
  content: string;
  analysis: MistakeElement[],
  highlights: HighlightElement[]
  isVisible: boolean;
  isActive: boolean;
  isGrammarlyActive: boolean;
  isNativeSpellcheckingActive: boolean;
  isBackgroundOverlayActive: boolean;
  isTextArea: boolean;
  isTextInput: boolean;
  isEditableContent: boolean;
}

export const createWidget = (identifier: string, editor: EditorElement, attribute: string) => {
  return {
    id: identifier,
    editor: editor,
    editorId: editor.id,
    attribute,
    shadowEditor: null,
    isVisible: false,
    isActive: false,
    isGrammarlyActive: false,
    isNativeSpellcheckingActive: false,
    isBackgroundOverlayActive: false,
    isTextArea: isTextArea(editor),
    isTextInput: isTextInput(editor),
    isEditableContent: isEditableContent(editor),
    content: getEditorContent(editor),
    analysis: [],
    highlights: []
  } as WidgetElement;
};
