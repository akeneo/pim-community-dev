import React from 'react';
import * as ReactDOM from 'react-dom';
import {Provider} from "react-redux";
import '@testing-library/jest-dom/extend-expect';
import {
    act,
    fireEvent,
    getAllByRole,
    getByRole,
    queryByRole,
    queryAllByRole,
    createEvent
} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import List from 'akeneopimstructure/js/attribute-option/components/List';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';

let container: HTMLElement;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
});

afterEach(() => {
    document.body.removeChild(container);
});

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
    test('it renders an empty attribute options list', async () => {
        await renderComponent([], false, jest.fn(), jest.fn(), jest.fn());
        expect(getByRole(container, 'attribute-options-list')).toBeEmptyDOMElement();
    });

    test('it renders a list of 2 options not sorted alphabetically by default', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => options};
        });

        await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn());

        const attributeOptions = getAllByRole(container, 'attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        expect(queryByRole(container, 'new-option-placeholder')).toBeNull();
    });

    test('it renders a list of 2 options sorted alphabetically by default', async () => {
        await renderComponent(options, true, jest.fn(), jest.fn(), jest.fn());

        const attributeOptions = getAllByRole(container, 'attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');
    });

    test('the list order can be toggled', async () => {
        await renderComponent(options, false, jest.fn(), jest.fn(), jest.fn());

        let attributeOptions = getAllByRole(container, 'attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        const toggleButton = getByRole(container, 'toggle-sort-attribute-option');
        await fireEvent.click(toggleButton);

        attributeOptions = getAllByRole(container, 'attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');
    });

    test('it displays a fake new option at the end when adding a new option', async () => {
        window.HTMLElement.prototype.scrollIntoView = jest.fn();
        const showNewOptionFormCallback = jest.fn();
        await renderComponent(options, false, jest.fn(), showNewOptionFormCallback, jest.fn());

        expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

        const addNewOptionButton = getByRole(container, 'add-new-attribute-option-button');
        await fireEvent.click(addNewOptionButton);
        const newOptionPlaceholder = getByRole(container, 'new-option-placeholder');

        expect(newOptionPlaceholder).toBeInTheDocument();
        expect(showNewOptionFormCallback).toHaveBeenNthCalledWith(1, true);

        const cancelNewOptionButton = getByRole(container, 'new-option-cancel');
        fireEvent.click(cancelNewOptionButton);
        expect(newOptionPlaceholder).not.toBeInTheDocument();
    });

    test('it allows option selection', async () => {
        const selectOptionCallback = jest.fn();
        const blueOptionId = 86;
        await renderComponent(options, false, selectOptionCallback, jest.fn(), jest.fn(), blueOptionId);

        expect(queryByRole(container, 'new-option-placeholder')).toBeNull();

        const optionItems = queryAllByRole(container, 'attribute-option-item');
        const blueOption = optionItems[0];
        const blackOption = optionItems[1];
        expect(blueOption).toHaveClass('AknAttributeOption-listItem--selected');
        expect(blackOption).not.toHaveClass('AknAttributeOption-listItem--selected');

        const optionLabels = queryAllByRole(container, 'attribute-option-item-label');
        const blackOptionLabel = optionLabels[1];
        await fireEvent.click(blackOptionLabel);

        const blackOptionId = 18;
        expect(selectOptionCallback).toHaveBeenNthCalledWith(1, blackOptionId)
    });

    test('it handles option reorder', async () => {
        const manuallySortAttributeOptions = jest.fn();
        await renderComponent(options, false, jest.fn(), jest.fn(), manuallySortAttributeOptions);

        const attributeOptionMoveHandle = getAllByRole(container, 'attribute-option-move-handle').shift();
        const attributeOptions = getAllByRole(container, 'attribute-option-item');

        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        await moveBlueOptionToBackOption(attributeOptionMoveHandle, attributeOptions);

        const sortedAttributeOptions = getAllByRole(container, 'attribute-option-item');
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

async function renderComponent(options, autoSortOptions, selectOptionCallback, showNewOptionFormCallback, manuallySortAttributeOptionsCallback, selectedOptionId = null) {
    await act(async () => {
        ReactDOM.render(
            <DependenciesProvider>
                <Provider store={createStoreWithInitialState({attributeOptions: options})}>
                    <AttributeContextProvider attributeId={8} autoSortOptions={autoSortOptions}>
                        <List
                            selectAttributeOption={selectOptionCallback}
                            isNewOptionFormDisplayed={false}
                            showNewOptionForm={showNewOptionFormCallback}
                            selectedOptionId={selectedOptionId}
                            deleteAttributeOption={jest.fn()}
                            manuallySortAttributeOptions={manuallySortAttributeOptionsCallback}
                        />
                    </AttributeContextProvider>
                </Provider>
            </DependenciesProvider>,
            container
        );
    });
}
