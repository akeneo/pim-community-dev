import {useAttributeGroupLabel} from "@akeneo-pim-community/settings-ui/src/hooks";
import {AttributeGroup} from "@akeneo-pim-community/settings-ui/src/models";
import {renderHookWithProviders} from "@akeneo-pim-community/shared/tests/front/unit/utils";

const renderUseAttributeGroupLabel = (group: AttributeGroup) => {
    return renderHookWithProviders(() => useAttributeGroupLabel(group));
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
        const group = {
            code: 'attribute_group_foo',
            labels: {
                'en_US': 'Foo'
            },
            sort_order: 1,
            attributes: [],
            attributes_sort_order: {},
            permissions: {
                view: [],
                edit: [],
            },
            meta: {id: 1234}
        };
        const {result} = renderUseAttributeGroupLabel(group);

        expect(result.current).toBe('Foo');
    });

    test('it returns the code when label does not exist for locale', () => {
        const group = {
            code: 'attribute_group_foo',
            labels: {},
            sort_order: 1,
            attributes: [],
            attributes_sort_order: {},
            permissions: {
                view: [],
                edit: [],
            },
            meta: {id: 1234}
        };
        const {result} = renderUseAttributeGroupLabel(group);

        expect(result.current).toBe('[attribute_group_foo]');
    });
});
