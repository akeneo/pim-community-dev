import InArray from 'pimfront/product-grid/domain/model/filter/operator/in-array';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal';

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
