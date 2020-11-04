import EditorElement, {
  convertHtmlContent,
  getEditorContent,
  isEditableContent,
  isTextArea,
  isTextInput,
  replaceContentFromRange,
} from '@akeneo-pim-ee/data-quality-insights/src/application/helper/EditorHighlight/EditorElement';

beforeEach(() => {
  jest.resetModules();
});

describe('EditorElement', () => {
  describe('isTextArea', () => {
    test('it returns true when the editor is a HTML TEXTAREA element', () => {
      const editor = document.createElement('textarea');
      expect(isTextArea(editor)).toBe(true);
    });
    test('it returns false when the editor is not a HTML TEXTAREA element', () => {
      const editor1: EditorElement = document.createElement('input');
      expect(isTextArea(editor1)).toBe(false);

      const editor2: EditorElement = document.createElement('div');
      editor2.setAttribute('contenteditable', 'true');
      expect(isTextArea(editor2)).toBe(false);
    });
  });

  describe('isTextInput', () => {
    test('it returns true when the editor is a HTML TEXT INPUT element', () => {
      const editor = document.createElement('input');
      editor.setAttribute('type', 'text');
      expect(isTextInput(editor)).toBe(true);
    });
    test('it returns false when the editor is not a HTML TEXT INPUT element', () => {
      const editor1 = document.createElement('textarea');
      expect(isTextInput(editor1)).toBe(false);

      const editor2 = document.createElement('div');
      editor2.setAttribute('contenteditable', 'true');
      expect(isTextInput(editor2)).toBe(false);

      const editor3 = document.createElement('input');
      editor2.setAttribute('type', 'file');
      expect(isTextInput(editor3)).toBe(false);
    });
  });

  describe('isEditableContent', () => {
    test('it returns true when the editor is a HTML CONTENT EDITABLE element', () => {
      const editor = document.createElement('div');
      editor.setAttribute('contenteditable', 'true');
      expect(isEditableContent(editor)).toBe(true);
    });
    test('it returns false when the editor is not a HTML CONTENT EDITABLE element', () => {
      const editor1 = document.createElement('textarea');
      expect(isEditableContent(editor1)).toBe(false);

      const editor2 = document.createElement('div');
      editor2.setAttribute('contenteditable', 'false');
      expect(isEditableContent(editor2)).toBe(false);

      const editor3 = document.createElement('div');
      expect(isEditableContent(editor3)).toBe(false);

      const editor4 = document.createElement('input');
      expect(isEditableContent(editor4)).toBe(false);

      const editor5 = document.createElement('span');
      editor5.setAttribute('contenteditable', 'true');
      // @ts-ignore
      expect(isEditableContent(editor5)).toBe(false);
    });
  });

  describe('getEditorContent', () => {
    test('it returns content of a HTML TEXTAREA element', () => {
      const editor = document.createElement('textarea');
      editor.value = 'My text content';

      expect(getEditorContent(editor)).toEqual('My text content');
    });

    test('it returns content of a HTML TEXT INPUT element', () => {
      const editor = document.createElement('input');
      editor.setAttribute('type', 'text');
      editor.value = 'My text content';

      expect(getEditorContent(editor)).toEqual('My text content');
    });

    test('it returns content of a HTML DIV element', () => {
      const editor = document.createElement('div');
      editor.setAttribute('contenteditable', 'true');
      editor.innerHTML = '<p>My HTML content</p>';

      expect(getEditorContent(editor)).toEqual('<p>My HTML content</p>');
    });

    test('it returns empty content of another HTML element', () => {
      const editor = document.createElement('div');
      editor.innerHTML = '<p>My HTML content</p>';

      expect(getEditorContent(editor)).toEqual('');

      const editor2 = document.createElement('input');
      editor2.setAttribute('type', 'checkbox');
      editor2.value = 'My text content';

      expect(getEditorContent(editor2)).toEqual('');
    });
  });

  describe('convertHtmlContent', () => {
    test('it converts HTML content into a well formatted plain text content', () => {
      expect(convertHtmlContent('')).toEqual('');

      const htmlContent =
        '<p><span>My</span> <span>text</span></p>\n<p><span>My<br></span> <span title="My title">text©</span></p><p><span>Mon</span> <span>texte accentué</span></p>';
      const expectedContent = 'My text\nMy\n text©\nMon texte accentué\n';
      expect(convertHtmlContent(htmlContent)).toEqual(expectedContent);
    });
    test('it does not convert content that should be HTML but does not contain HTML tags', () => {
      const htmlContent =
        'Length: Short\nLining: 100% polyester\nModel: Model is 179 cm and wears a size 36\nCervical Shape: Deep V-neck\nTotal length: 88 cm in size 36\nAdditional Info: zip, lined\nHandle Type: Sleeveless\nComposition: 100% nylon\nCare instructions: hand wash';
      expect(convertHtmlContent(htmlContent)).toEqual(htmlContent);
    });
  });

  describe('replaceContentFromRange', () => {
    test('it returns new content with replaced element', () => {
      expect(replaceContentFromRange('Hello world!', 'test', 6, 11)).toEqual('Hello test!');

      // weird cases
      expect(replaceContentFromRange('Hello world!', 'test', 11, 6)).toEqual('Hello worldtestworld!');
      expect(replaceContentFromRange('Hello world!', 'test', 15, 6)).toEqual('Hello world!testworld!');
      expect(replaceContentFromRange('Hello world!', 'test', 15, 12)).toEqual('Hello world!test');
      expect(replaceContentFromRange('Hello world!', 'test', -2, -6)).toEqual('testHello world!');
      expect(replaceContentFromRange('Hello world!', 'test', 0, 0)).toEqual('testHello world!');
    });
  });
});
