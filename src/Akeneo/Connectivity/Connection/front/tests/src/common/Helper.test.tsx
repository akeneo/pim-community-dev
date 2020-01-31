import * as React from 'react';
import {create} from 'react-test-renderer';
import {Helper, HelperLink, HelperTitle} from '@src/common';

describe('Helper', () => {
    it('should render', () => {
        const component = create(
            <Helper>
                <HelperTitle></HelperTitle>
                content
                <HelperLink />
            </Helper>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
