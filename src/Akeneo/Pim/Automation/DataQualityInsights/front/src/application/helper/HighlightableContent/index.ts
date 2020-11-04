export enum HighlightableElementType {
  UNKNOWN = 'unknown',
  TEXTAREA = 'textarea',
  TEXT = 'text',
  RICHTEXT = 'richtext',
}

export const getElementType = (element: HTMLElement | null) => {
  if (element === null) {
    return HighlightableElementType.UNKNOWN;
  }

  if (element.tagName === 'TEXTAREA') {
    return HighlightableElementType.TEXTAREA;
  }

  if (element.tagName === 'INPUT' && element.getAttribute('type') === 'text') {
    return HighlightableElementType.TEXT;
  }

  if (element.tagName === 'DIV' && element.getAttribute('contenteditable') === 'true') {
    return HighlightableElementType.RICHTEXT;
  }

  return HighlightableElementType.UNKNOWN;
};
