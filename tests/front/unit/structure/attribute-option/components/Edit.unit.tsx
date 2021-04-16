import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByRole, queryAllByRole} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import Edit from 'akeneopimstructure/js/attribute-option/components/Edit';
import {AttributeContextProvider, LocalesContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from "akeneopimstructure/js/attribute-option/model";

declare global {
    namespace NodeJS {
        interface Global {
            fetch: any;
        }
    }
}

let container: HTMLElement;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
});

afterEach(() => {
    document.body.removeChild(container);
    global.fetch && global.fetch.mockClear();
    delete global.fetch;
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

describe('Edit an attribute option', () => {
    test('it renders an option form', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => [
                    {"code":"en_US","label":"English (United States)"},
                    {"code":"fr_FR","label":"French (France)"}
                ]};
        });

        const saveCallback = jest.fn();

        await act(async () => {
            ReactDOM.render(
                <DependenciesProvider>
                    <AttributeContextProvider attributeId={8} autoSortOptions={true}>
                        <LocalesContextProvider>
                            <Edit option={option} saveAttributeOption={saveCallback} />
                        </LocalesContextProvider>
                    </AttributeContextProvider>
                </DependenciesProvider>,
                container
            );
        });

        const translations = queryAllByRole(container, 'attribute-option-label');
        expect(translations.length).toBe(2);

        const saveButton = getByRole(container, 'save-options-translations');
        expect(saveButton).toBeInTheDocument();

        await fireEvent.change(translations[0], {target: {value: 'Black 2'}});
        await fireEvent.click(saveButton);
        let expectedOption: AttributeOption = expect.objectContaining({
            id: 80,
            code: 'black',
            optionValues: {
                en_US: {
                    id: 1,
                    value: 'Black 2',
                    locale: 'en_US',
                },
                fr_FR: {
                    id: 2,
                    value: 'Noir',
                    locale: 'fr_FR',
                },
            },
        });
        expect(saveCallback).toHaveBeenNthCalledWith(1, expectedOption);
    });
});
