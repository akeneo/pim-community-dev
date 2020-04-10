import {hideLoadingMaskMiddleware} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/middleware/loading-mask';
import {
  SAVED_FAMILY_MAPPING_FAIL,
  SAVED_FAMILY_MAPPING_SUCCESS
} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/save-family-mapping';

describe('Infrastructure > Middleware > Loading Mask', () => {
  it('hides the loading mask when the action SAVED_FAMILY_MAPPING_SUCCESS is dispatched', () => {
    const callback = jest.fn();
    const store = jest.fn();
    const next = jest.fn();
    const action = {
      type: SAVED_FAMILY_MAPPING_SUCCESS
    };

    hideLoadingMaskMiddleware(callback)(store)(next)(action);

    expect(callback).toHaveBeenCalled();
  });

  it('hides the loading mask when the action SAVED_FAMILY_MAPPING_FAIL is dispatched', () => {
    const callback = jest.fn();
    const store = jest.fn();
    const next = jest.fn();
    const action = {
      type: SAVED_FAMILY_MAPPING_FAIL
    };

    hideLoadingMaskMiddleware(callback)(store)(next)(action);

    expect(callback).toHaveBeenCalled();
  });

  it('does not hide the loading mask when the action SAVED_FAMILY_MAPPING_FAIL is dispatched', () => {
    const callback = jest.fn();
    const store = jest.fn();
    const next = jest.fn();
    const action = {
      type: 'ANOTHER_ACTION'
    };
    hideLoadingMaskMiddleware(callback)(store)(next)(action);

    expect(callback).not.toHaveBeenCalled();
  });
});
