import * as React from 'react';
import {create} from 'react-test-renderer';
import {Translate} from '../../../../application/shared/translate/translate.component';
import {TranslateContext} from '../../../../application/shared/translate/translate.context';

describe('Translate component', () => {
    test('Matches the snapshot', () => {
        const translate = () => 'Bonjour le monde';
        const component = create(
            <TranslateContext.Provider value={translate}>
                <Translate id='hello.world' />
            </TranslateContext.Provider>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
