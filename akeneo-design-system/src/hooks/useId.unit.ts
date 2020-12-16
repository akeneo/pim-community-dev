import {renderHook} from '@testing-library/react-hooks';
import {useId} from './useId';

test('It returns an Uuid with a prefix if provided', async () => {
  const {result} = renderHook(() => useId('nice_'));

  await expect(result.current).toMatch(
    /^nice_[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/
  );
});

test('It returns an Uuid without a prefix if not provided', async () => {
  const {result} = renderHook(() => useId());

  await expect(result.current).toMatch(
    /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/
  );
});
