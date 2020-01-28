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

export const setEditorContent = (editor: EditorElement, content: string, replacement: string, start: number, end: number) => {
  if (isTextArea(editor) || isTextInput(editor)) {
  if (isFirefox || !document.queryCommandSupported('insertText')) {
      // @ts-ignore
      editor.value = replaceContentFromRange(content, replacement, start, end);
    }
    else {
      // @ts-ignore
      editor.selectionStart = start;
      // @ts-ignore
      editor.selectionEnd = end;
      editor.focus();

      if (!document.execCommand('insertText', false, replacement)) {
        // @ts-ignore
        editor.setRangeText(replacement, start, end);
      }
    }

    editor.dispatchEvent(new Event('input', { bubbles: true }));
    editor.dispatchEvent(new Event('change', { bubbles: true }));
    return;
  }

  editor.innerHTML = content;

  editor.dispatchEvent(new Event('input', { bubbles: true }));
  editor.dispatchEvent(new Event('change', { bubbles: true }));
};

const  replaceContentFromRange = (content: string, replacement: string, start: number, end: number) => {
  const subContentStart = content.substring(0, start);
  const subContentEnd = content.substring(end);

  return `${subContentStart}${replacement}${subContentEnd}`;
};

const isFirefox = (navigator.userAgent.toLowerCase().indexOf('firefox') > -1);
