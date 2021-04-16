import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByRole, queryByRole, queryAllByRole, createEvent} from '@testing-library/react';
import ListItem from 'akeneopimstructure/js/attribute-option/components/ListItem';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';

let container: HTMLElement;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
});

afterEach(() => {
    document.body.removeChild(container);
});

const blackOption: AttributeOption = {
    id: 80,
    code: 'black',
    optionValues: {
        en_US: {
            id: 1,
            value: 'Black',
            locale: 'en_US',
        },
    },
};

const blueOption: AttributeOption = {
    id: 14,
    code: 'blue',
    optionValues: {
        en_US: {
            id: 3,
            value: 'Blue',
            locale: 'en_US',
        },
    },
};

describe('Attribute options list item', () => {
    test('it renders a list item', async () => {
        const onSelectCallback = jest.fn();

        await renderComponent(blackOption, onSelectCallback, jest.fn(), jest.fn(), jest.fn(), false, null, jest.fn());

        const attributeOption = getByRole(container, 'attribute-option-item');
        expect(attributeOption).toHaveClass('AknAttributeOption-listItem--selected');

        const attributeOptionLabel = getByRole(container, 'attribute-option-item-label');
        await fireEvent.click(attributeOptionLabel);
        expect(onSelectCallback).toHaveBeenNthCalledWith(1, 80);
    });

    test('it allows attribute option to be deleted', async () => {
        const deleteAttributeOptionCallback = jest.fn();

        await renderComponent(blackOption, jest.fn(), deleteAttributeOptionCallback, jest.fn(), jest.fn(), false, null, jest.fn());

        const deleteButton = getByRole(container, 'attribute-option-delete-button');
        let deleteConfirmationModal = queryByRole(container, 'attribute-option-delete-confirmation-modal');
        expect(deleteConfirmationModal).not.toBeInTheDocument();

        await fireEvent.click(deleteButton);

        deleteConfirmationModal = getByRole(container, 'attribute-option-delete-confirmation-modal');
        const confirmDeleteButton = getByRole(container, 'attribute-option-confirm-delete-button');
        const cancelButtons = queryAllByRole(container, 'attribute-option-confirm-cancel-button');

        expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
        expect(deleteConfirmationModal).toBeInTheDocument();
        expect(cancelButtons).toHaveLength(2);

        await fireEvent.click(confirmDeleteButton);
        expect(deleteAttributeOptionCallback).toHaveBeenCalled();
    });

    test('the attribute option deletion can be cancelled with the 2 cancel buttons', async () => {
        const deleteAttributeOptionCallback = jest.fn();

        await renderComponent(blackOption, jest.fn(), deleteAttributeOptionCallback, jest.fn(), jest.fn(), false, null, jest.fn());

        const deleteButton = getByRole(container, 'attribute-option-delete-button');
        await fireEvent.click(deleteButton);
        const deleteConfirmationModal = getByRole(container, 'attribute-option-delete-confirmation-modal');
        let cancelButtons = queryAllByRole(container, 'attribute-option-confirm-cancel-button');

        expect(cancelButtons).toHaveLength(2);

        await fireEvent.click(cancelButtons[0]);
        expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
        expect(deleteConfirmationModal).not.toBeInTheDocument();

        await fireEvent.click(deleteButton);
        cancelButtons = queryAllByRole(container, 'attribute-option-confirm-cancel-button');
        await fireEvent.click(cancelButtons[1]);
        expect(deleteAttributeOptionCallback).not.toHaveBeenCalled();
        expect(deleteConfirmationModal).not.toBeInTheDocument();
    });

    test('an attribute option will not be moved upwards if the treshold is not met', async () => {
        const moveOptionCallback = jest.fn();
        await renderComponent(blueOption, jest.fn(), jest.fn(), moveOptionCallback, jest.fn(), false, {code: 'black', index: 0}, jest.fn());
        const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

        fireEvent.dragOver(attributeOptionMoveHandle, {
            target: {
                getBoundingClientRect: jest.fn().mockImplementation(() => {
                    return {bottom: 10, top: 0};
                }),
                clientY: 4
            },
        });

        expect(moveOptionCallback).not.toHaveBeenCalled();
    });

    test('an attribute option will not be moved downwards if the treshold is not met', async () => {
        const moveOptionCallback = jest.fn();
        await renderComponent(blueOption, jest.fn(), jest.fn(), moveOptionCallback, jest.fn(), false, {code: 'black', index: 2}, jest.fn());
        const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

        fireEvent.dragOver(attributeOptionMoveHandle, {
            target: {
                getBoundingClientRect: jest.fn().mockImplementation(() => {
                    return {bottom: 10, top: 0};
                }),
                clientY: 6
            },
        });

        expect(moveOptionCallback).not.toHaveBeenCalled();
    });

    test('an attribute option cannot replace itself', async () => {
        const moveOptionCallback = jest.fn();
        await renderComponent(blackOption, jest.fn(), jest.fn(), moveOptionCallback, jest.fn(), false, {code: 'black', index: 2}, jest.fn());
        const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

        fireEvent.dragOver(attributeOptionMoveHandle);

        expect(moveOptionCallback).not.toHaveBeenCalled();
    });

    test('an attribute option cannot be moved if the options are sorted alphabetically', async () => {
        const setDragItem = jest.fn();
        await renderComponent(blackOption, jest.fn(), jest.fn(), jest.fn(), jest.fn(), true, setDragItem);
        const attributeOptionMoveHandle = getByRole(container, 'attribute-option-move-handle');

        fireEvent.dragStart(attributeOptionMoveHandle);
        expect(setDragItem).not.toHaveBeenCalled();

        fireEvent.dragEnd(attributeOptionMoveHandle);
        expect(setDragItem).not.toHaveBeenCalled();
    });
});

async function renderComponent(option, selectAttributeOptionCallback, deleteAttributeOptionCallback, moveAttributeOptionCallback, validateMoveAttributeOption, autoSort, dragItem, setDragItem) {
    await act(async () => {
        ReactDOM.render(
            <DependenciesProvider>
                <AttributeContextProvider attributeId={8} autoSortOptions={autoSort}>
                    <ListItem
                        data={option}
                        selectAttributeOption={selectAttributeOptionCallback}
                        isSelected={true}
                        deleteAttributeOption={deleteAttributeOptionCallback}
                        moveAttributeOption={moveAttributeOptionCallback}
                        validateMoveAttributeOption={validateMoveAttributeOption}
                        dragItem={dragItem}
                        setDragItem={setDragItem}
                        index={1}
                    />
                </AttributeContextProvider>
            </DependenciesProvider>,
            container
        );
    });
}
