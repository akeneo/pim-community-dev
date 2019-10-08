import * as React from 'react';
import {create} from 'react-test-renderer';
import {Page} from '../../../application/common';

describe('Page component', () => {
    test('Matches the snapshot', () => {
        const component = create(<Page />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
