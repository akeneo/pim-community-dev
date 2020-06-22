import React from 'react';
import {Provider} from "react-redux";
import '@testing-library/jest-dom/extend-expect';
import {act, createEvent, fireEvent, render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import List from 'akeneopimstructure/js/attribute-option/components/List';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOptionsContextProvider} from "../../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/contexts";

const options = [
    {
        "id": 86,
        "code": "blue",
        "optionValues": {
            "en_US": {"id":255,"locale":"en_US","value":"Blue"},
            "fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu"}
        }
    },
    {
        "id": 18,
        "code": "black",
        "optionValues": {
            "en_US": {"id":252,"locale":"en_US","value":"Black"},
            "fr_FR":{"id":253,"locale":"fr_FR","value":"Noir"}
        }
    },
];

describe('Attribute options list', () => {
    beforeAll(() => {
        global.fetch = jest.fn();
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        global.fetch.mockRestore();
        jest.restoreAllMocks();
        jest.resetAllMocks();
    });

    test('it renders an empty attribute options list', async () => {
        const {getByRole} = renderComponent([], false, jest.fn(), jest.fn(), jest.fn());
        expect(getByRole('attribute-options-list')).toBeEmpty();
    });

    test('it renders a list of 2 options not sorted alphabetically by default', async () => {
        jest.spyOn(global, 'fetch').mockImplementationOnce(route => {
            return {json: () => options};
        });

        const {getAllByRole, queryByRole} = renderComponent(options, false, jest.fn(), jest.fn(), jest.fn());

        const attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        expect(queryByRole('new-option-placeholder')).toBeNull();
    });

    test('it renders a list of 2 options sorted alphabetically by default', async () => {
        const {getAllByRole} = renderComponent(options, true, jest.fn(), jest.fn(), jest.fn());

        const attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');
    });

    test('the list order can be toggled', async () => {
        const {getAllByRole, getByRole} = renderComponent(options, false, jest.fn(), jest.fn(), jest.fn());

        let attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        const toggleButton = getByRole('toggle-sort-attribute-option');

        act(() => {
            fireEvent.click(toggleButton);
        });

        attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');
    });

    test('it displays a fake new option at the end when adding a new option', async () => {
        window.HTMLElement.prototype.scrollIntoView = jest.fn();
        const showNewOptionFormCallback = jest.fn();
        const {queryByRole, getByRole} = renderComponent(options, false, jest.fn(), showNewOptionFormCallback, jest.fn());

        expect(queryByRole('new-option-placeholder')).toBeNull();

        const addNewOptionButton = getByRole('add-new-attribute-option-button');
        act(() => {
            fireEvent.click(addNewOptionButton);
        });
        const newOptionPlaceholder = getByRole('new-option-placeholder');

        expect(newOptionPlaceholder).toBeInTheDocument();
        expect(showNewOptionFormCallback).toHaveBeenNthCalledWith(1, true);

        const cancelNewOptionButton = getByRole('new-option-cancel');
        act(() => {
            fireEvent.click(cancelNewOptionButton);
        });
        expect(newOptionPlaceholder).not.toBeInTheDocument();
    });

    test('it allows option selection', async () => {
        const selectOptionCallback = jest.fn();
        const blueOptionId = 86;
        const {queryByRole, queryAllByRole} = renderComponent(options, false, selectOptionCallback, jest.fn(), jest.fn(), blueOptionId);

        expect(queryByRole('new-option-placeholder')).toBeNull();

        const optionItems = queryAllByRole('attribute-option-item');
        const blueOption = optionItems[0];
        const blackOption = optionItems[1];
        expect(blueOption).toHaveClass('AknAttributeOption-listItem--selected');
        expect(blackOption).not.toHaveClass('AknAttributeOption-listItem--selected');

        const optionLabels = queryAllByRole('attribute-option-item-label');
        const blackOptionLabel = optionLabels[1];

        act(() => {
            fireEvent.click(blackOptionLabel);
        });

        const blackOptionId = 18;
        expect(selectOptionCallback).toHaveBeenNthCalledWith(1, blackOptionId)
    });

    test('it handles option reorder', async () => {
        const manuallySortAttributeOptions = jest.fn();
        const {getAllByRole} = renderComponent(options, false, jest.fn(), jest.fn(), manuallySortAttributeOptions);

        const attributeOptionMoveHandle = getAllByRole('attribute-option-move-handle').shift();
        const attributeOptions = getAllByRole('attribute-option-item');

        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        act(() => {
            moveBlueOptionToBackOption(attributeOptionMoveHandle, attributeOptions);
        });

        const sortedAttributeOptions = getAllByRole('attribute-option-item');
        expect(sortedAttributeOptions.length).toBe(2);
        expect(sortedAttributeOptions[0].textContent).toBe('black');
        expect(sortedAttributeOptions[1].textContent).toBe('blue');

        expect(manuallySortAttributeOptions).toHaveBeenNthCalledWith(1, [
            {
                "id": 18,
                "code": "black",
                "optionValues": {
                    "en_US": {"id":252,"locale":"en_US","value":"Black"},
                    "fr_FR":{"id":253,"locale":"fr_FR","value":"Noir"}
                }
            },
            {
                "id": 86,
                "code": "blue",
                "optionValues": {
                    "en_US": {"id":255,"locale":"en_US","value":"Blue"},
                    "fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu"}
                }
            },
        ]);
    });
});

async function moveBlueOptionToBackOption(attributeOptionMoveHandle, attributeOptions) {
    const dragStartEvent = createEvent.dragStart(attributeOptionMoveHandle);
    const setDragImage = jest.fn();
    Object.assign(dragStartEvent, {
        dataTransfer: {
            setDragImage,
        },
    });
    await fireEvent(attributeOptionMoveHandle, dragStartEvent);
    await fireEvent.dragOver(attributeOptions[1], {
        target: {
            getBoundingClientRect: jest.fn().mockImplementation(() => {
                return {bottom: 10, top: 0};
            }),
            clientY: 6
        },
    });
    await fireEvent.drop(attributeOptionMoveHandle);
    await fireEvent.dragEnd(attributeOptionMoveHandle);
}

const renderComponent = (options, autoSortOptions, selectOptionCallback, showNewOptionFormCallback, manuallySortAttributeOptionsCallback, selectedOptionId = null)  => {
    return render(
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions: options})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={autoSortOptions}>
                    <AttributeOptionsContextProvider attributeId={8}>
                        <List
                            selectAttributeOption={selectOptionCallback}
                            isNewOptionFormDisplayed={false}
                            showNewOptionForm={showNewOptionFormCallback}
                            selectedOptionId={selectedOptionId}
                            deleteAttributeOption={jest.fn()}
                            manuallySortAttributeOptions={manuallySortAttributeOptionsCallback}
                        />
                    </AttributeOptionsContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
}
