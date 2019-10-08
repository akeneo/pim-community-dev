import * as React from 'react';
import {create} from 'react-test-renderer';
import {Header} from '../../../application/common';

describe('Header component', () => {
    test('Matches the snapshot', () => {
        const component = create(<Header />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
