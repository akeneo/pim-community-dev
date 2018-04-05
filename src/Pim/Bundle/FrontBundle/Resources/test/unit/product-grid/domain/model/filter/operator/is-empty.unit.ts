import IsEmpty from 'pimfront/product-grid/domain/model/filter/operator/is-empty';
import Equal from 'pimfront/product-grid/domain/model/filter/operator/equal-boolean';

describe('>>>DOMAIN --- model - operator - is empty', () => {
  test('I can create a new is empty operator', () => {
    expect(IsEmpty.create().identifier).toBe('EMPTY');
    expect(IsEmpty.create().needValue).toBe(false);
  });

  test('I can compare with other operators', () => {
    expect(IsEmpty.create().equals(IsEmpty.create())).toBe(true);
    expect(IsEmpty.create().equals(Equal.create())).toBe(false);
  });
});
