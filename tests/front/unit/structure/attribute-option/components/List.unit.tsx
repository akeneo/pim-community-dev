import React from 'react';
import {Provider} from 'react-redux';
import '@testing-library/jest-dom/extend-expect';
import {act, createEvent, fireEvent, render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {createStoreWithInitialState} from 'akeneopimstructure/js/attribute-option/store/store';
import List from 'akeneopimstructure/js/attribute-option/components/List';
import {
    AttributeContextProvider,
    AttributeOptionsContextProvider
} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

const options = [
    {
        id: 86,
        code: 'blue',
        optionValues: {
            'en_US': {id:255, locale: 'en_US', value: 'Blue'},
            'fr_FR': {id:256, locale: 'fr_FR', value: 'Bleu'}
        }
    },
    {
        id: 18,
        code: 'black',
        optionValues: {
            'en_US': {id:252, locale: 'en_US', value: 'Black'},
            'fr_FR': {id:253, locale: 'fr_FR', value: 'Noir'}
        }
    },
];

describe('Attribute options list', () => {
    beforeAll(() => {
        window.HTMLElement.prototype.scrollIntoView = jest.fn();
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
        jest.resetAllMocks();
    });

    test('it renders an empty attribute options list', () => {
        const {getByRole} = renderComponent([], false);
        expect(getByRole('attribute-options-list')).toBeEmpty();
    });

    test('it renders a list of 2 options not sorted alphabetically by default', () => {
        const {getAllByRole, queryByRole} = renderComponent(options, false);

        const attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        expect(queryByRole('new-option-placeholder')).toBeNull();
    });

    test('it renders a list of 2 options sorted alphabetically by default', () => {
        const {getAllByRole} = renderComponent(options, true);

        const attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('black');
        expect(attributeOptions[1].textContent).toBe('blue');
    });

    test('the list order can be toggled', () => {
        const {getAllByRole, getByRole} = renderComponent(options, false);

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

    test('it displays a fake new option at the end when adding a new option', () => {
        const {queryByRole, getByRole} = renderComponent(options, false);

        expect(queryByRole('new-option-placeholder')).toBeNull();

        const addNewOptionButton = getByRole('add-new-attribute-option-button');
        act(() => {
            fireEvent.click(addNewOptionButton);
        });

        expect(queryByRole('new-option-placeholder')).toBeInTheDocument();

        const cancelNewOptionButton = getByRole('new-option-cancel');
        act(() => {
            fireEvent.click(cancelNewOptionButton);
        });
        expect(queryByRole('new-option-placeholder')).not.toBeInTheDocument();
    });

    test('it allows option selection', () => {
        const {queryByRole, queryAllByRole} = renderComponent(options, false);

        expect(queryByRole('new-option-placeholder')).toBeNull();

        let optionItems = queryAllByRole('attribute-option-item');
        let blueOption = optionItems[0];
        let blackOption = optionItems[1];

        expect(blueOption).toHaveClass('AknAttributeOption-listItem AknAttributeOption-listItem--selected');
        expect(blackOption).not.toHaveClass('AknAttributeOption-listItem AknAttributeOption-listItem--selected');

        const optionLabels = queryAllByRole('attribute-option-item-label');
        const blackOptionLabel = optionLabels[1];

        act(() => {
            fireEvent.click(blackOptionLabel);
        });

        optionItems = queryAllByRole('attribute-option-item');
        blueOption = optionItems[0];
        blackOption = optionItems[1];

        expect(blueOption).not.toHaveClass('AknAttributeOption-listItem AknAttributeOption-listItem--selected');
        expect(blackOption).toHaveClass('AknAttributeOption-listItem AknAttributeOption-listItem--selected');
    });

    test('it handles option reorder', async () => {
        const {getAllByRole} = renderComponent(options, false);

        let attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        const attributeOptionMoveHandle = getAllByRole('attribute-option-move-handle').shift();

        attributeOptions = getAllByRole('attribute-option-item');
        expect(attributeOptions.length).toBe(2);
        expect(attributeOptions[0].textContent).toBe('blue');
        expect(attributeOptions[1].textContent).toBe('black');

        await act(async () => {
            await moveBlueOptionToBackOption(attributeOptionMoveHandle, attributeOptions);
        });

        const sortedAttributeOptions = getAllByRole('attribute-option-item');
        expect(sortedAttributeOptions.length).toBe(2);
        expect(sortedAttributeOptions[0].textContent).toBe('black');
        expect(sortedAttributeOptions[1].textContent).toBe('blue');
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

const renderComponent = (
    options: AttributeOption[]|null,
    autoSortOptions: boolean
)  => {
    return render(
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions: options})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={autoSortOptions}>
                    <AttributeOptionsContextProvider>
                        <List />
                    </AttributeOptionsContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};
