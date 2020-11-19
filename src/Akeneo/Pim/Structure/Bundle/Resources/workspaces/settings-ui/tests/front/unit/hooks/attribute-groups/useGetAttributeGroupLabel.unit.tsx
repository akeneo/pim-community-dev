import {useGetAttributeGroupLabel} from '@akeneo-pim-community/settings-ui/src/hooks';
import {AttributeGroup} from '@akeneo-pim-community/settings-ui/src/models';
import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {anAttributeGroup} from '../../../utils/provideAttributeGroupHelper';

const renderUseAttributeGroupLabel = (_group: AttributeGroup) => {
  return renderHookWithProviders(() => useGetAttributeGroupLabel());
};

describe('useAttributeGroupLabel', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns the proper label for the en_US locale', () => {
    const group = anAttributeGroup('attribute_group_foo', 1234, {
      en_US: 'Foo',
    });
    const {result} = renderUseAttributeGroupLabel(group);

    const getLabel = result.current;
    expect(getLabel(group)).toBe('Foo');
  });

  test('it returns the code when label does not exist for locale', () => {
    const group = anAttributeGroup('attribute_group_foo');
    const {result} = renderUseAttributeGroupLabel(group);

    const getLabel = result.current;
    expect(getLabel(group)).toBe('[attribute_group_foo]');
  });
});
