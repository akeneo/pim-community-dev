import {All, Equal, InArray} from 'pimfront/product-grid/domain/model/filter/operator';

describe('>>>DOMAIN --- model - operator - all', () => {
  test('I can create a new all operator', () => {
    expect(All.create().identifier).toBe('ALL');
    expect(All.create().needValue).toBe(false);
  });

  test('I can compare with other operators', () => {
    expect(All.create().equals(All.create())).toBe(true);
    expect(All.create().equals(Equal.create())).toBe(false);
  });
});

describe('>>>DOMAIN --- model - operator - equal', () => {
  test('I can create a new all operator', () => {
    expect(Equal.create().identifier).toBe('=');
    expect(Equal.create().needValue).toBe(true);
  });

  test('I can compare with other operators', () => {
    expect(Equal.create().equals(Equal.create())).toBe(true);
    expect(Equal.create().equals(All.create())).toBe(false);
  });
});

describe('>>>DOMAIN --- model - operator - in array', () => {
  test('I can create a new all operator', () => {
    expect(InArray.create().identifier).toBe('IN');
    expect(InArray.create().needValue).toBe(true);
  });

  test('I can compare with other operators', () => {
    expect(InArray.create().equals(InArray.create())).toBe(true);
    expect(InArray.create().equals(Equal.create())).toBe(false);
  });
});
