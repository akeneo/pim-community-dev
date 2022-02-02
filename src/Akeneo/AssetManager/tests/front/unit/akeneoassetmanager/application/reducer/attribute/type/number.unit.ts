import {reducer} from 'akeneoassetmanager/application/reducer/attribute/type/number';

test('I can update the decimals allowed property of the attribute', () => {
  const state = {type: 'number'};
  const newState = reducer(state, 'decimals_allowed', true);

  expect(newState).toEqual({
    type: 'number',
    decimals_allowed: true,
  });
});

test('I can update the min value property of the attribute', () => {
  const state = {type: 'number'};
  const newState = reducer(state, 'min_value', 10);

  expect(newState).toEqual({
    type: 'number',
    min_value: 10,
  });
});

test('I can update the max value property of the attribute', () => {
  const state = {type: 'number'};
  const newState = reducer(state, 'max_value', 100);

  expect(newState).toEqual({
    type: 'number',
    max_value: 100,
  });
});

test('It ignores invalid additional_property', () => {
  const state = {type: 'number'};
  const newState = reducer(state, 'michel', ['gif', 'png']);

  expect(newState).toBe(state);
});
