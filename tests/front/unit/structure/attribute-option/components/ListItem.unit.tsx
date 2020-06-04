import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByRole, queryByRole, queryAllByRole} from '@testing-library/react';
import ListItem from 'akeneopimstructure/js/attribute-option/components/ListItem';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

let container: HTMLElement;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
});

afterEach(() => {
    document.body.removeChild(container);
});

const option: AttributeOption = {
    id: 80,
    code: 'black',
    optionValues: {
        en_US: {
            id: 1,
            value: 'Black',
            locale: 'en_US',
        },
        fr_FR: {
            id: 2,
            value: 'Noir',
            locale: 'fr_FR',
        },
    },
};

describe('Attribute options list item', () => {
    test('it renders a list item', async () => {
        const onSelectCallback = jest.fn();

        await renderComponent(onSelectCallback, jest.fn());

        const attributeOption = getByRole(container, 'attribute-option-item');
        expect(attributeOption).toHaveClass('AknAttributeOption-listItem--selected');

        const attributeOptionLabel = getByRole(container, 'attribute-option-item-label');
        await fireEvent.click(attributeOptionLabel);
        expect(onSelectCallback).toHaveBeenNthCalledWith(1, 80);
    });

    test('it allows attribute option to be deleted', async () => {
        const deleteAttributeOptionCallback = jest.fn();

        await renderComponent(jest.fn(), deleteAttributeOptionCallback);

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

        await renderComponent(jest.fn(), deleteAttributeOptionCallback);

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
});

async function renderComponent(selectAttributeOptionCallback, deleteAttributeOptionCallback) {
    await act(async () => {
        ReactDOM.render(
            <DependenciesProvider>
                <ListItem
                    data={option}
                    selectAttributeOption={selectAttributeOptionCallback}
                    isSelected={true}
                    deleteAttributeOption={deleteAttributeOptionCallback}
                />
            </DependenciesProvider>,
            container
        );
    });
}
