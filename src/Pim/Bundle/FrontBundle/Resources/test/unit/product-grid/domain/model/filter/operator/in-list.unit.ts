import InList from 'pimfront/product-grid/domain/model/filter/operator/in-list';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal-boolean';

describe('>>>DOMAIN --- model - operator - in list', () => {
  test('I can create a new all operator', () => {
    expect(InList.create().identifier).toBe('IN');
    expect(InList.create().needValue).toBe(true);
  });

  test('I can compare with other operators', () => {
    expect(InList.create().equals(InList.create())).toBe(true);
    expect(InList.create().equals(Equal.create())).toBe(false);
  });
});
