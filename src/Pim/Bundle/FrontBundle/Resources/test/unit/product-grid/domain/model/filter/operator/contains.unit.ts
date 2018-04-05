import Contains from 'pimfront/product-grid/domain/model/filter/operator/contains';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal-boolean';

describe('>>>DOMAIN --- model - operator - contains', () => {
  test('I can create a new contains operator', () => {
    expect(Contains.create().identifier).toBe('CONTAINS');
    expect(Contains.create().needValue).toBe(true);
  });

  test('I can compare with other operators', () => {
    expect(Contains.create().equals(Contains.create())).toBe(true);
    expect(Contains.create().equals(Equal.create())).toBe(false);
  });
});
