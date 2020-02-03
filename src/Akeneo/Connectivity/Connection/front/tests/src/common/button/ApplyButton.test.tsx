import * as React from 'react';
import {create} from 'react-test-renderer';
import {ApplyButton} from '@src/common';

describe('ApplyButton', () => {
    it('should have the apply style', () => {
        const component = create(<ApplyButton onClick={() => undefined}>content</ApplyButton>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
