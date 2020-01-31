import * as React from 'react';
import {create} from 'react-test-renderer';
import {Translate} from '@src/shared/translate/Translate';
import {TranslateContext} from '@src/shared/translate/translate-context';

describe('Translate', () => {
    it('should display the translation', () => {
        const translate = jest.fn().mockReturnValue('No product.');

        const component = create(
            <TranslateContext.Provider value={translate}>
                <Translate id='hello.world' />
            </TranslateContext.Provider>
        );

        expect(translate).toBeCalledWith('hello.world', {}, 1);
        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should handle pluralization and placeholders', () => {
        const translate = jest.fn().mockReturnValue('9 products');

        const component = create(
            <TranslateContext.Provider value={translate}>
                <Translate id='hello.world' count={9} placeholders={{count: '9'}} />
            </TranslateContext.Provider>
        );

        expect(translate).toBeCalledWith('hello.world', {count: '9'}, 9);
        expect(component.toJSON()).toMatchSnapshot();
    });
});
