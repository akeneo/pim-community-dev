import {mount} from 'enzyme';
import * as React from 'react';
import {create} from 'react-test-renderer';
import {BreadcrumbRouterLink, RouterContext} from '../../../../../src/application/shared/router';

describe('BreadcrumbRouterLink', () => {
    it('should render', () => {
        const component = create(<BreadcrumbRouterLink route='a_route'>content</BreadcrumbRouterLink>);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should redirect to a route', () => {
        const router = {
            generate: jest.fn().mockReturnValue('/an_url'),
            redirect: jest.fn(),
        };

        const component = mount(
            <RouterContext.Provider value={router}>
                <BreadcrumbRouterLink route='a_route'>content</BreadcrumbRouterLink>
            </RouterContext.Provider>
        );
        component.find(BreadcrumbRouterLink).simulate('click');

        expect(router.generate).toBeCalledWith('a_route');
        expect(router.redirect).toBeCalledWith('/an_url');
    });
});
