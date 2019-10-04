import * as React from 'react';
import {create} from 'react-test-renderer';
import {ApplyButton} from '../../../../application/common';

describe('Button component', () => {
    test('Matches the snapshot', () => {
        const component = create(<ApplyButton onClick={() => undefined} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
