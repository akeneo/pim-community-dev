import DoesNotContain from 'pimfront/product-grid/domain/model/filter/operator/does-not-contain';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal-boolean';

describe('>>>DOMAIN --- model - operator - all', () => {
  test('I can create a new all operator', () => {
    expect(DoesNotContain.create().identifier).toBe('DOES NOT CONTAIN');
    expect(DoesNotContain.create().needValue).toBe(true);
  });

  test('I can compare with other operators', () => {
    expect(DoesNotContain.create().equals(DoesNotContain.create())).toBe(true);
    expect(DoesNotContain.create().equals(Equal.create())).toBe(false);
  });
});
