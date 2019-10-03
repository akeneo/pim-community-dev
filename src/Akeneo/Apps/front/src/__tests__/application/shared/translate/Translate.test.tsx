import * as React from 'react';
import {create} from 'react-test-renderer';
import {TranslateContext} from '../../../../application/shared/translate/translate-context';
import {Translate} from '../../../../application/shared/translate/Translate';

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
