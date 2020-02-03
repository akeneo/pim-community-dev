import * as React from 'react';
import {create} from 'react-test-renderer';
import {PageContent} from '@src/common';

describe('Page', () => {
    it('should render', () => {
        const component = create(<PageContent>content</PageContent>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
