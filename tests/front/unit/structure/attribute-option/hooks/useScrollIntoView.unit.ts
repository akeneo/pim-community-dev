import {renderHook} from '@testing-library/react-hooks';
import {useScrollIntoView} from 'akeneopimstructure/js/attribute-option/hooks/useScrollIntoView';

describe('useScrollIntoView', () => {
  const scrollIntoViewMockFn = jest.fn();

  beforeAll(() => {
    window.HTMLElement.prototype.scrollIntoView = scrollIntoViewMockFn;
  });

  beforeEach(() => {
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.clearAllMocks();
  });

  it('should scroll when current ref is not null', () => {
    const ref = {
      current: document.createElement('div'),
    };
    renderHook(() => useScrollIntoView(ref));

    expect(scrollIntoViewMockFn).toHaveBeenCalled();
  });

  it('should not scroll when current ref is null', () => {
    const ref = {
      current: null,
    };
    renderHook(() => useScrollIntoView(ref));

    expect(scrollIntoViewMockFn).not.toHaveBeenCalled();
  });
});
