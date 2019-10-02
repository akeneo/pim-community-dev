import * as React from 'react';
import { create } from 'react-test-renderer';
import { Translate } from '../../../../application/component/shared/translate';

describe('Translate component', () => {
    test('Matches the snapshot', () => {
        const translate = create(<Translate id='hello.world' />);
        expect(translate.toJSON()).toMatchSnapshot();
    });
});
