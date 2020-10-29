import HighlightElement, {
  createHighlight,
  getTextNodesIn,
  getTextRange,
  isIntersectingHighlight,
} from '@akeneo-pim-ee/data-quality-insights/src/application/helper/EditorHighlight/HighlightElement';
import EditorElement from '@akeneo-pim-ee/data-quality-insights/src/application/helper/EditorHighlight/EditorElement';
import Range from '../../../__mocks__/Range';

beforeEach(() => {
  jest.resetModules();
});

describe('HighlightElement.', () => {
  describe('createHighlight.', () => {
    test('create highlight', () => {
      const highlightId = 'my_id';
      const mistake = {
        text: 'my text',
        type: 'issue_type',
        globalOffset: 0,
        offset: 0,
        line: 1,
        suggestions: [],
      };

      givenCreateRangeIsSupported();

      const editorElement = givenSimpleTextEditor('my text');

      const range = new Range();
      range.selectNodeContents(editorElement);
      range.setStart(editorElement.firstChild, 0);
      range.setEnd(editorElement.firstChild, 7);

      const highlight = createHighlight(highlightId, mistake, editorElement);

      expect(highlight.classList).toEqual(['AknEditorHighlight-mark--issue_type']);

      // TODO: fix this assert
      // expect(highlight.domRange).toEqual(range);

      expect(highlight.mistake).toBe(mistake);
      expect(highlight.id).toBe(highlightId);
      expect(highlight.isActive).toBe(false);
    });
  });

  describe('getTextRange', () => {
    test('get text range', () => {
      givenCreateRangeIsSupported();
      const editorElement = givenSimpleTextEditor('my text');

      const range = getTextRange(editorElement, 0, 7);

      expect(range.collapsed).toBe(false);
      expect(range.endContainer).toEqual(editorElement.firstChild);
      expect(range.startContainer).toEqual(editorElement.firstChild);
      expect(range.startOffset).toBe(0);
      expect(range.endOffset).toBe(7);
    });

    test('get text range with rich text editor', () => {
      givenCreateRangeIsSupported();
      const editorElement = givenRichTextEditor('<p><span>typos</span> <span>hapen</span></p>');

      const range = getTextRange(editorElement, 0, 11);
      const expectedEndContainer = document.createTextNode('hapen');
      const expectedStartContainer = document.createTextNode('typos');

      expect(range.collapsed).toBe(false);
      expect(range.startContainer).toEqual(expectedStartContainer);
      expect(range.startOffset).toBe(0);
      expect(range.endContainer).toEqual(expectedEndContainer);
      expect(range.endOffset).toBe(5);

      const editorElement2 = givenRichTextEditor('<p><span>typos</span> <span>hapen</span></p>');
      const range2 = getTextRange(editorElement2, 6, 11);
      const expectedContainer = document.createTextNode('hapen');

      expect(range2.collapsed).toBe(false);
      expect(range2.endContainer).toEqual(expectedContainer);
      expect(range2.startOffset).toBe(0);
      expect(range2.startContainer).toEqual(expectedContainer);
      expect(range2.endOffset).toBe(5);

      const editorElement3 = givenRichTextEditor(
        '<p><span>typos</span> <span>hapen</span></p><p><span>typos</span> <span>hapen</span></p>'
      );
      const range3 = getTextRange(editorElement3, 6, 11);
      const range4 = getTextRange(editorElement3, 18, 23);
      const expectedContainer3 = document.createTextNode('hapen');
      const expectedContainer4 = document.createTextNode('hapen');

      expect(range3.collapsed).toBe(false);
      expect(range3.endContainer).toEqual(expectedContainer3);
      expect(range3.startOffset).toBe(0);
      expect(range3.startContainer).toEqual(expectedContainer3);
      expect(range3.endOffset).toBe(5);

      expect(range4.collapsed).toBe(false);
      expect(range4.endContainer).toEqual(expectedContainer4);
      expect(range4.startOffset).toBe(0);
      expect(range4.startContainer).toEqual(expectedContainer4);
      expect(range4.endOffset).toBe(5);
    });
  });

  describe('getTextNodesIn', () => {
    test('get text nodes in text node', () => {
      const node = document.createTextNode('my text');
      const textNodes = getTextNodesIn(node);

      expect(textNodes.length).toBe(1);
      expect(textNodes).toEqual([node]);
    });

    test('get text nodes in simple text editor', () => {
      const editorElement = givenSimpleTextEditor('my text');
      const textNodes = getTextNodesIn(editorElement);

      const expectedTextNodes = [
        document.createTextNode('my text'),
        document.createTextNode('\n'), // <DIV> editor node break line
      ];

      expect(textNodes.length).toBe(2);
      expect(textNodes).toEqual(expectedTextNodes);
    });

    test('get text nodes in rich text editor', () => {
      const editorElement = givenRichTextEditor('<p><span>my</span> <span>text</span></p>');
      const textNodes = getTextNodesIn(editorElement);

      const expectedTextNodes = [
        document.createTextNode('my'),
        document.createTextNode(' '),
        document.createTextNode('text'),
        document.createTextNode('\n'), // <P> node break line
        document.createTextNode('\n'), // <DIV> editor node break line
      ];

      expect(textNodes.length).toBe(5);
      expect(textNodes).toEqual(expectedTextNodes);
    });
  });

  describe('isIntersectingHighlight', () => {
    test('coordinates are intersecting with highlight', () => {
      const highlight = givenHighlight();
      const result = isIntersectingHighlight(10, 10, highlight);
      expect(result).toBe(true);
    });

    test('according to the intersecting margin, coordinates are intersecting with highlight', () => {
      const highlight = givenHighlight();
      const result = isIntersectingHighlight(4, 16, highlight);
      expect(result).toBe(true);
    });

    test('coordinates are not intersecting with highlight', () => {
      const highlight = givenHighlight();
      const result = isIntersectingHighlight(2, 18, highlight);
      expect(result).toBe(false);
    });
  });
});

const givenSimpleTextEditor = (content: string): EditorElement => {
  const editorElement = document.createElement('div');
  editorElement.textContent = content;

  return editorElement;
};

const givenRichTextEditor = (htmlContent: string): EditorElement => {
  const editorElement = document.createElement('div');
  editorElement.insertAdjacentHTML('beforeend', htmlContent);

  return editorElement;
};

const givenHighlight = (): HighlightElement => {
  return {
    id: '',
    classList: [],
    // @ts-ignore
    mistake: jest.fn(),
    isActive: false,
    // @ts-ignore
    domRange: {
      collapsed: false,
      // @ts-ignore
      endContainer: jest.fn<Element>(),
      endOffset: 10,
      // @ts-ignore
      startContainer: jest.fn<Element>(),
      startOffset: 0,
      getBoundingClientRect: jest.fn(() => {
        return {
          left: 5,
          right: 15,
          top: 10,
          bottom: 15,
          height: 5,
          width: 10,
          x: 5,
          y: 10,
        } as DOMRect;
      }),
    },
  };
};

const givenCreateRangeIsSupported = (): void => {
  if (!document.createRange) {
    document.createRange = () => {
      return new Range();
    };
  }
};
