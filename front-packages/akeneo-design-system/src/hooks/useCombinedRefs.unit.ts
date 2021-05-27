import {renderHook} from '@testing-library/react-hooks';
import {useCombinedRefs} from './useCombinedRefs';

test('It combines refs', () => {
  const ref = {current: 'hello'};
  const anotherRef = () => ref;

  const {result} = renderHook(() => useCombinedRefs(ref, anotherRef, null));

  const combinedRef = result.current;

  expect(combinedRef.current).toBe(ref.current);
});
