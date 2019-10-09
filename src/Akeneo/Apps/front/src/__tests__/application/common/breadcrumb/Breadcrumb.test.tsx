import * as React from 'react';
import {create} from 'react-test-renderer';
import {Breadcrumb, BreadcrumbItem} from '../../../../application/common';

describe('Button component', () => {
    test('Matches the snapshot', () => {
        const component = create(
            <Breadcrumb>
                <BreadcrumbItem onClick={() => undefined}>Is clickable</BreadcrumbItem>
                <BreadcrumbItem>Is just a label</BreadcrumbItem>
                <BreadcrumbItem onClick={() => undefined}>Is clickable and the last item</BreadcrumbItem>
            </Breadcrumb>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
