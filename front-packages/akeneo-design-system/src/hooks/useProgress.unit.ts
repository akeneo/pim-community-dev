import {renderHook, act} from '@testing-library/react-hooks';
import {useProgress} from './useProgress';

test('It handle progress', () => {
  const {result} = renderHook(() => useProgress(['choose', 'edit', 'confirm']));

  const [isCurrent, next, previous] = result.current;

  expect(isCurrent('choose')).toBe(true);
  expect(isCurrent('edit')).toBe(false);
  expect(isCurrent('confirm')).toBe(false);

  void act(() => {
    previous();
  });

  const [isCurrentTryPreviousFirst] = result.current;

  expect(isCurrentTryPreviousFirst('choose')).toBe(true);
  expect(isCurrentTryPreviousFirst('edit')).toBe(false);
  expect(isCurrentTryPreviousFirst('confirm')).toBe(false);

  void act(() => {
    next();
  });

  const [isCurrentSecondStep] = result.current;

  expect(isCurrentSecondStep('choose')).toBe(false);
  expect(isCurrentSecondStep('edit')).toBe(true);
  expect(isCurrentSecondStep('confirm')).toBe(false);

  void act(() => {
    next();
  });

  const [isCurrentThirdStep] = result.current;

  expect(isCurrentThirdStep('choose')).toBe(false);
  expect(isCurrentThirdStep('edit')).toBe(false);
  expect(isCurrentThirdStep('confirm')).toBe(true);

  void act(() => {
    next();
  });

  const [isCurrentTryAfterLast] = result.current;

  expect(isCurrentTryAfterLast('choose')).toBe(false);
  expect(isCurrentTryAfterLast('edit')).toBe(false);
  expect(isCurrentTryAfterLast('confirm')).toBe(true);

  void act(() => {
    previous();
  });

  const [isCurrentPrevious] = result.current;

  expect(isCurrentPrevious('choose')).toBe(false);
  expect(isCurrentPrevious('edit')).toBe(true);
  expect(isCurrentPrevious('confirm')).toBe(false);
});

test('It cannot handle non unique step codes', () => {
  expect(() => {
    useProgress(['choose', 'choose', 'confirm']);
  }).toThrow('Steps array cannot have duplicated names');
});

test('It cannot handle empty step codes', () => {
  expect(() => {
    useProgress([]);
  }).toThrow('Steps array cannot be empty');
});
