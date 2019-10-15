import * as React from 'react';
import {create} from 'react-test-renderer';
import {Page} from '../../../../src/application/common';

describe('Page', () => {
    it('should render', () => {
        const component = create(<Page>content</Page>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
