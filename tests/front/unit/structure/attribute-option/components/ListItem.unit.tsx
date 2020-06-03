import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByRole} from '@testing-library/react';
import ListItem from 'akeneopimstructure/js/attribute-option/components/ListItem';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';

let container: HTMLElement;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
});

afterEach(() => {
    document.body.removeChild(container);
});

describe('Attribute options list item', () => {
    test('it renders a list item', async () => {
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

        const onSelectCallback = jest.fn();

        await act(async () => {
            ReactDOM.render(
                <ListItem data={option} onSelectAttributeOption={onSelectCallback} isSelected={true} />,
                container
            );
        });

        const attributeOption = getByRole(container, 'attribute-option-item');
        expect(attributeOption).toHaveClass('AknAttributeOption-listItem--selected');

        const attributeOptionLabel = getByRole(container, 'attribute-option-item-label');
        await fireEvent.click(attributeOptionLabel);
        expect(onSelectCallback).toHaveBeenNthCalledWith(1, 80);
    });
});
