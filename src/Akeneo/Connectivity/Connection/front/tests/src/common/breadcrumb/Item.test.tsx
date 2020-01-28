import * as React from 'react';
import {create} from 'react-test-renderer';
import {BreadcrumbItem} from '@src/common';

describe('BreadcrumbItem', () => {
    it('should render', () => {
        const component = create(<BreadcrumbItem>content</BreadcrumbItem>);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should be clickable', () => {
        const component = create(<BreadcrumbItem onClick={() => undefined}>content</BreadcrumbItem>);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should have the last item style', () => {
        const component = create(<BreadcrumbItem isLast={true}>content</BreadcrumbItem>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
