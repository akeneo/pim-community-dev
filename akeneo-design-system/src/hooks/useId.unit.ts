import {renderHook} from '@testing-library/react-hooks';
import {useId} from './useId';

test('It returns an Uuid with a prefix if provided', () => {
  const {result} = renderHook(() => useId('nice_'));

  expect(result.current).toMatch(/^nice_[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/);
});

test('It returns an Uuid without a prefix if not provided', () => {
  const {result} = renderHook(() => useId());

  expect(result.current).toMatch(/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/);
});
