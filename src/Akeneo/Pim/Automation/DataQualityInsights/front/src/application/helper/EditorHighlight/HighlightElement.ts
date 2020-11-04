import EditorElement from './EditorElement';
import MistakeElement from './MistakeElement';
import {HTML_BLOCK_LEVEL_ELEMENTS_LIST, HTML_BREAKING_LINE_ELEMENTS_LIST} from '../../constant';

export default interface HighlightElement {
  id: string;
  classList: string[];
  domRange: Range;
  mistake: MistakeElement;
  isActive: boolean;
}

export interface HighlightsCollection {
  [id: string]: HighlightElement;
}

export const createHighlight = (id: string, mistake: MistakeElement, element: EditorElement) => {
  const mistakeClass = `AknEditorHighlight-mark--${mistake.type}`;
  const range = getTextRange(element, mistake.globalOffset, mistake.globalOffset + mistake.text.length);

  return {
    id,
    classList: [mistakeClass],
    domRange: range,
    mistake,
    isActive: false,
  };
};

export const getTextRange = (el: EditorElement, start: number, end: number) => {
  const range = document.createRange();
  range.selectNodeContents(el);

  const textNodes = getTextNodesIn(el);
  let foundStart = false;
  let foundEnd = false;
  let charCount = 0;
  let endCharCount = 0;
  let content = '';

  textNodes.forEach((textNode, index) => {
    if (foundStart && foundEnd) {
      return;
    }

    content = textNode.wholeText;
    endCharCount = charCount + content.length;

    if (
      !foundStart &&
      start >= charCount &&
      (start < endCharCount || (start === charCount && index <= textNodes.length - 1))
    ) {
      range.setStart(textNode, start - charCount);
      foundStart = true;
    }

    if (foundStart && end <= endCharCount) {
      range.setEnd(textNode, end - charCount);
      foundEnd = true;
    }

    charCount = endCharCount;
  });

  return range;
};

export const getTextNodesIn = (node: Node): Text[] => {
  if (node.nodeType === Node.TEXT_NODE) {
    return [node as Text];
  }

  let nodes: Text[] = [];
  Array.from(node.childNodes).forEach((n: Node) => {
    nodes = [...nodes, ...getTextNodesIn(n)];
  });

  if (
    HTML_BREAKING_LINE_ELEMENTS_LIST.includes(node.nodeName.toLowerCase()) ||
    HTML_BLOCK_LEVEL_ELEMENTS_LIST.includes(node.nodeName.toLowerCase())
  ) {
    const breakingLineNode: Text = document.createTextNode('\n');

    nodes = [...nodes, breakingLineNode];
  }

  return nodes;
};

const HIGHLIGHT_INTERSECTING_MARGIN = 2;

export const isIntersectingHighlight = (x: number, y: number, highlight: HighlightElement) => {
  //@fixme very slow performance, take too much time for requestAnimationFrame (message: [Violation] 'requestAnimationFrame' handler took 90ms)
  const rect: DOMRect = highlight.domRange.getBoundingClientRect();

  return (
    x >= rect.left - HIGHLIGHT_INTERSECTING_MARGIN &&
    x <= rect.right + HIGHLIGHT_INTERSECTING_MARGIN &&
    y >= rect.top - HIGHLIGHT_INTERSECTING_MARGIN &&
    y <= rect.bottom + HIGHLIGHT_INTERSECTING_MARGIN
  );
};
