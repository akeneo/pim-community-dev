import EditorElement from "./EditorElement";
import MistakeElement from "./MistakeElement";

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
  const mistakeClass = `AknSpellCheck-mark--${mistake.type}`;
  const range = getTextRange(
    element,
    mistake.globalOffset,
    mistake.globalOffset + mistake.text.length
  );

  return {
    id,
    classList: [mistakeClass],
    domRange: range,
    mistake,
    isActive: false
  };
};

const getTextRange = (el: EditorElement, start: number, end: number) => {
  const range = document.createRange();
  range.selectNodeContents(el);

  const textNodes = getTextNodesIn(el);
  let foundStart = false;
  let charCount = 0;
  let endCharCount = 0;

  for (let i = 0, textNode; (textNode = textNodes[i++] as Text); ) {
    endCharCount = charCount + textNode.length;
    if (
      !foundStart &&
      start >= charCount &&
      (start < endCharCount ||
        (start === endCharCount && i <= textNodes.length))
    ) {
      range.setStart(textNode, start - charCount);
      foundStart = true;
    }
    if (foundStart && end <= endCharCount) {
      range.setEnd(textNode, end - charCount);
      break;
    }
    charCount = endCharCount;
  }

  return range;
};

const getTextNodesIn = (node: Node) => {
  if (node.nodeType === Node.TEXT_NODE) {
    return [node];
  }
  return Array.from(node.childNodes).filter(n => n.nodeType === n.TEXT_NODE)
};
