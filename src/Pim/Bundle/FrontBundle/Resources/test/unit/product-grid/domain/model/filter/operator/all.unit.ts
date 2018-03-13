import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal';

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
