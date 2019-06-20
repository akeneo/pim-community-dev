import {reducer} from 'akeneoreferenceentity/application/reducer/attribute/type/text';

describe('akeneo > reference entity > application > reducer > attribute --- edit', () => {
  test('I can update the textarea property of the attribute', () => {
    const state = {
      type: 'text',
      is_rich_text_editor: false,
    };
    const newState = reducer(state, 'is_textarea', true);

    expect(newState).toEqual({
      type: 'text',
      is_textarea: true,
      is_rich_text_editor: false,
      regular_expression: null,
      validation_rule: 'none',
    });
  });

  test('I can update the textarea property of the attribute to true', () => {
    const state = {
      type: 'text',
      is_rich_text_editor: true,
    };
    const newState = reducer(state, 'is_textarea', false);

    expect(newState).toEqual({
      type: 'text',
      is_textarea: false,
      is_rich_text_editor: false,
    });
  });

  test('I can update the is rich text editor property of the attribute to true', () => {
    const state = {
      type: 'text',
      is_rich_text_editor: false,
    };
    const newState = reducer(state, 'is_rich_text_editor', true);

    expect(newState).toEqual({
      type: 'text',
      is_rich_text_editor: true,
    });
  });

  test("I can't update the is rich text editor property of the attribute to true without textarea", () => {
    const state = {
      type: 'text',
      is_textarea: false,
      is_rich_text_editor: false,
    };
    const newState = reducer(state, 'is_rich_text_editor', true);

    expect(newState).toEqual({
      type: 'text',
      is_textarea: false,
      is_rich_text_editor: false,
    });
  });

  test('I can update the max length property of the attribute', () => {
    const state = {
      type: 'text',
      max_length: null,
    };
    const newState = reducer(state, 'max_length', 120);

    expect(newState).toEqual({
      type: 'text',
      max_length: 120,
    });
  });

  test('I can update the validation rule property of the attribute to none', () => {
    const state = {
      type: 'text',
      is_textarea: false,
    };
    const newState = reducer(state, 'validation_rule', 'none');

    expect(newState).toEqual({
      type: 'text',
      is_textarea: false,
      validation_rule: 'none',
      regular_expression: null,
    });
  });

  test('I can update the validation rule property of the attribute to regular expression', () => {
    const state = {
      type: 'text',
      is_textarea: false,
    };
    const newState = reducer(state, 'validation_rule', 'regular_expression');

    expect(newState).toEqual({
      type: 'text',
      is_textarea: false,
      validation_rule: 'regular_expression',
    });
  });

  test("I can't update the validation rule property of the attribute to regular expression", () => {
    const state = {
      type: 'text',
      is_textarea: true,
    };
    const newState = reducer(state, 'validation_rule', 'regular_expression');

    expect(newState).toEqual({
      type: 'text',
      is_textarea: true,
    });
  });

  test('I can update the regular expression property of the attribute', () => {
    const state = {
      type: 'text',
      is_textarea: false,
      validation_rule: 'regular_expression',
    };
    const newState = reducer(state, 'regular_expression', 'hey!');

    expect(newState).toEqual({
      type: 'text',
      is_textarea: false,
      validation_rule: 'regular_expression',
      regular_expression: 'hey!',
    });
  });

  test("I can't update the regular expression property of the attribute", () => {
    const state = {
      type: 'text',
      is_textarea: false,
      validation_rule: 'none',
    };
    const newState = reducer(state, 'regular_expression', 'hey!');

    expect(newState).toEqual({
      type: 'text',
      is_textarea: false,
      validation_rule: 'none',
    });
  });

  test('It ignores invalid additional_property', () => {
    const state = {type: 'text'};
    const newState = reducer(state, 'michel', true);

    expect(newState).toBe(state);
  });
});
