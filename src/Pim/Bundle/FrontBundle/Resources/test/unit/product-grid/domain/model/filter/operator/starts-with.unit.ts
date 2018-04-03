import StartsWith from 'pimfront/product-grid/domain/model/filter/operator/starts-with';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal';

describe('>>>DOMAIN --- model - operator - starts with', () => {
  test('I can create a new starts with operator', () => {
    expect(StartsWith.create().identifier).toBe('STARTS WITH');
    expect(StartsWith.create().needValue).toBe(true);
  });

  test('I can compare with other operators', () => {
    expect(StartsWith.create().equals(StartsWith.create())).toBe(true);
    expect(StartsWith.create().equals(Equal.create())).toBe(false);
  });
});
