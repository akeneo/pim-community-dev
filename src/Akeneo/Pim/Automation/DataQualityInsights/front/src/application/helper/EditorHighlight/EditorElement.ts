import {getTextRange} from './HighlightElement';
import {HTML_BLOCK_LEVEL_ELEMENTS_LIST, HTML_BREAKING_LINE_ELEMENTS_LIST} from '../../constant';

type EditorElement = HTMLTextAreaElement | HTMLDivElement | HTMLInputElement;

export default EditorElement;

export const isTextArea = (editor: EditorElement) => {
  return editor && editor.tagName === 'TEXTAREA';
};

export const isTextInput = (editor: EditorElement) => {
  return editor && editor.tagName === 'INPUT' && editor.getAttribute('type') === 'text';
};

export const isEditableContent = (editor: EditorElement) => {
  return editor && editor.tagName === 'DIV' && editor.getAttribute('contenteditable') === 'true';
};

export const getEditorContent = (editor: EditorElement) => {
  if (isTextArea(editor) || isTextInput(editor)) {
    // @ts-ignore
    return editor.value;
  }

  if (isEditableContent(editor)) {
    return editor.innerHTML;
  }

  return '';
};

export const convertHtmlContent = (htmlContent: string): string => {
  const domParser = new DOMParser();
  let content = htmlContent;

  const isHTML = new RegExp(/(<([^>]+)>)/i).test(content);
  if (!isHTML) {
    return content;
  }

  content = content.replace('\n', '');
  content = content.replace(new RegExp(`(<\\/(${HTML_BLOCK_LEVEL_ELEMENTS_LIST.join('|')})>)`, 'gim'), '$1\n');
  content = content.replace(new RegExp(`(<(${HTML_BREAKING_LINE_ELEMENTS_LIST.join('|')})\\s*[\\/]?>)`, 'gim'), '$1\n');

  let doc = domParser.parseFromString(content, 'text/html');
  content = doc.body.textContent || '';

  return content;
};

export const setEditorContent = (
  editor: EditorElement,
  content: string,
  replacement: string,
  start: number,
  end: number
) => {
  if (isTextArea(editor) || isTextInput(editor)) {
    setTextEditorContent(editor, content, replacement, start, end);
  } else if (isEditableContent(editor)) {
    setRichTextEditorContent(editor, replacement, start, end);
  }
};

export const setTextEditorContent = (
  editor: EditorElement,
  content: string,
  replacement: string,
  start: number,
  end: number
) => {
  if (!isTextArea(editor) && !isTextInput(editor)) {
    return;
  }

  if (isFirefox || !document.queryCommandSupported('insertText')) {
    // @ts-ignore
    editor.value = replaceContentFromRange(content, replacement, start, end);
  } else {
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

  editor.dispatchEvent(new Event('input', {bubbles: true}));
  editor.dispatchEvent(new Event('change', {bubbles: true}));
};

export const setRichTextEditorContent = (editor: EditorElement, replacement: string, start: number, end: number) => {
  if (!isEditableContent(editor)) {
    return;
  }

  const range = getTextRange(editor, start, end);
  const selection = isFirefox ? window.getSelection() : document.getSelection();

  if (!selection) {
    return;
  }

  selection.removeAllRanges();
  selection.addRange(range);

  if (isFirefox) {
    range.deleteContents();
  }

  document.execCommand('insertHTML', false, replacement);

  if (isFirefox) {
    // Firefox keeps the text node of the previous content. It creates a bug with the rendering of the highlights
    const parentNode = range.startContainer.parentNode;
    if (parentNode) {
      parentNode.removeChild(range.startContainer);
    }
  }

  editor.dispatchEvent(new Event('input', {bubbles: true}));
  editor.dispatchEvent(new Event('change', {bubbles: true}));
};

export const replaceContentFromRange = (content: string, replacement: string, start: number, end: number) => {
  const subContentStart = content.substring(0, start);
  const subContentEnd = content.substring(end);

  return `${subContentStart}${replacement}${subContentEnd}`;
};

const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
