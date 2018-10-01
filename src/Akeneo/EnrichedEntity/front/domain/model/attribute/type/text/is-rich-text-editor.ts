import {InvalidArgumentError} from 'akeneoreferenceentity/domain/model/attribute/type/text';
export type NormalizedIsRichTextEditor = boolean;

export class IsRichTextEditor {
  private constructor(readonly isRichTextEditor: boolean) {
    if (!IsRichTextEditor.isValid(isRichTextEditor)) {
      throw new InvalidArgumentError('IsRichTextEditor need to be a boolean');
    }
    Object.freeze(this);
  }
  public static isValid(value: any): boolean {
    return typeof value === 'boolean';
  }
  public static createFromNormalized(normalizedIsRichTextEditor: NormalizedIsRichTextEditor) {
    return new IsRichTextEditor(normalizedIsRichTextEditor);
  }
  public normalize(): NormalizedIsRichTextEditor {
    return this.isRichTextEditor;
  }
  public static createFromBoolean(isRichTextEditor: boolean) {
    return IsRichTextEditor.createFromNormalized(isRichTextEditor);
  }
  public booleanValue(): boolean {
    return this.normalize();
  }
}
