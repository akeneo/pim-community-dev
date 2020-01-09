type EditorElement = HTMLTextAreaElement | HTMLDivElement | HTMLInputElement;

export default EditorElement;

export const isTextArea = (editor: EditorElement) => {
  return editor && editor.tagName === "TEXTAREA";
};

export const isTextInput = (editor: EditorElement) => {
  return (
    editor &&
    editor.tagName === "INPUT" &&
    editor.getAttribute("type") === "text"
  );
};

export const isEditableContent = (editor: EditorElement) => {
  return editor && editor.isContentEditable;
};

export const getEditorContent = (editor: EditorElement) => {
  if (isTextArea(editor) || isTextInput(editor)) {
    // @ts-ignore
    return editor.value;
  }

  return editor.innerHTML;
};

