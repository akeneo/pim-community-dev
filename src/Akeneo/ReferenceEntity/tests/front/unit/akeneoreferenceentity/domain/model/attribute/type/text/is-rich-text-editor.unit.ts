import {IsRichTextEditor} from 'akeneoreferenceentity/domain/model/attribute/type/text/is-rich-text-editor';

describe('akeneo > attribute > domain > model > attribute > type > text --- IsRichTextEditor', () => {
  test('I can create a IsRichTextEditor from normalized', () => {
    expect(IsRichTextEditor.createFromNormalized(false).normalize()).toEqual(false);
    expect(IsRichTextEditor.createFromNormalized(true).normalize()).toEqual(true);
    expect(() => IsRichTextEditor.createFromNormalized('true')).toThrow();
  });
  test('I can validate a IsRichTextEditor', () => {
    expect(IsRichTextEditor.isValid(true)).toEqual(true);
    expect(IsRichTextEditor.isValid(false)).toEqual(true);
    expect(IsRichTextEditor.isValid('12')).toEqual(false);
    expect(IsRichTextEditor.isValid('1')).toEqual(false);
    expect(IsRichTextEditor.isValid(1)).toEqual(false);
    expect(IsRichTextEditor.isValid(0)).toEqual(false);
    expect(IsRichTextEditor.isValid(undefined)).toEqual(false);
    expect(IsRichTextEditor.isValid({})).toEqual(false);
  });
  test('I can create a IsRichTextEditor from boolean', () => {
    expect(IsRichTextEditor.createFromBoolean(true).booleanValue()).toEqual(true);
    expect(IsRichTextEditor.createFromBoolean(false).booleanValue()).toEqual(false);
  });
});
