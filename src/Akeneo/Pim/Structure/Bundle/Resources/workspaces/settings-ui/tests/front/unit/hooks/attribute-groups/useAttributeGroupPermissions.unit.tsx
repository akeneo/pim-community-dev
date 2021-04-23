import {useAttributeGroupPermissions} from '@akeneo-pim-community/settings-ui/src/hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const renderUseAttributeGroupPermissions = () => {
  return renderHookWithProviders(() => useAttributeGroupPermissions());
};

describe('useAttributeGroupPermissions', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it checks user permissions on Attribute Groups', () => {
    const {result} = renderUseAttributeGroupPermissions();

    expect(result.current).toHaveProperty('indexGranted');
    expect(result.current).toHaveProperty('createGranted');
    expect(result.current).toHaveProperty('editGranted');
    expect(result.current).toHaveProperty('removeGranted');
    expect(result.current).toHaveProperty('sortGranted');
    expect(result.current).toHaveProperty('addAttributeGranted');
    expect(result.current).toHaveProperty('removeAttributeGranted');
    expect(result.current).toHaveProperty('historyGranted');
  });
});
