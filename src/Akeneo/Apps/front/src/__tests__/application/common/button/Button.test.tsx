import * as React from 'react';
import {create} from 'react-test-renderer';
import {Button} from '../../../../application/common/button/Button';

describe('Button component', () => {
    test('Matches the snapshot', () => {
        const component = create(<Button onClick={() => undefined} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
