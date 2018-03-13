import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';

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
