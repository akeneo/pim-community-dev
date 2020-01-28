import * as React from 'react';
import {create} from 'react-test-renderer';
import {Breadcrumb, BreadcrumbItem} from '@src/common';

describe('Breadcrumb', () => {
    it('should render multiple BreadcrumbItem', () => {
        const component = create(
            <Breadcrumb>
                <BreadcrumbItem>content</BreadcrumbItem>
                <BreadcrumbItem>content</BreadcrumbItem>
            </Breadcrumb>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
