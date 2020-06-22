import React from 'react';
import {Provider} from "react-redux";
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, render} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import Edit from 'akeneopimstructure/js/attribute-option/components/Edit';
import {AttributeContextProvider, LocalesContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from "akeneopimstructure/js/attribute-option/model";
import {AttributeOptionsContextProvider} from "../../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/contexts";
import {createStoreWithInitialState} from "../../../../../../public/bundles/akeneopimstructure/js/attribute-option/store/store";

declare global {
    namespace NodeJS {
        interface Global {
            fetch: any;
        }
    }
}

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

const renderEditWithContext = () => {
    return render(
        <DependenciesProvider>
            <Provider store={createStoreWithInitialState({attributeOptions: [option]})}>
                <AttributeContextProvider attributeId={8} autoSortOptions={true}>
                    <LocalesContextProvider>
                        <AttributeOptionsContextProvider attributeId={8}>
                            <Edit option={option} />
                        </AttributeOptionsContextProvider>
                    </LocalesContextProvider>
                </AttributeContextProvider>
            </Provider>
        </DependenciesProvider>
    );
};

describe('Edit an attribute option', () => {
    beforeAll(() => {
        global.fetch = jest.fn();
    });

    beforeEach(() => {
        jest.clearAllMocks();
    });

    afterAll(() => {
        jest.restoreAllMocks();
        delete global.fetch;
    })

    test('it renders an option form', async () => {
        global.fetch = jest.fn().mockImplementationOnce(route => {
            return {json: () => [
                    {"code":"en_US","label":"English (United States)"},
                    {"code":"fr_FR","label":"French (France)"}
                ]};
        });

        const saveCallback = jest.fn();

        const {queryAllByRole, getByRole} = renderEditWithContext();

        const translations = queryAllByRole('attribute-option-label');
        expect(translations.length).toBe(2);

        const saveButton = getByRole('save-options-translations');
        expect(saveButton).toBeInTheDocument();

        act(() => {
            fireEvent.change(translations[0], {target: {value: 'Black 2'}});
        });

        act(() => {
            fireEvent.click(saveButton);
        })

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