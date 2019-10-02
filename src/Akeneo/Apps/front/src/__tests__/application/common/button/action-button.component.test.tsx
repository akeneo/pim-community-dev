import * as React from 'react';
import {create} from 'react-test-renderer';
import {ActionButton} from '../../../../application/common/button/action-button.component';

describe('Button component', () => {
    test('Matches the snapshot', () => {
        const component = create(<ActionButton onClick={() => undefined} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
