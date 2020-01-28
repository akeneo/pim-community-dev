import {act, render} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import * as React from 'react';
import {create} from 'react-test-renderer';
import {BreadcrumbRouterLink, RouterContext} from '@src/shared/router';

describe('BreadcrumbRouterLink', () => {
    it('should render', () => {
        const component = create(<BreadcrumbRouterLink route='a_route'>content</BreadcrumbRouterLink>);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should redirect to a route', async () => {
        const router = {
            generate: jest.fn().mockReturnValue('/an_url'),
            redirect: jest.fn(),
        };

        const {getByText} = render(
            <RouterContext.Provider value={router}>
                <BreadcrumbRouterLink route='a_route'>content</BreadcrumbRouterLink>
            </RouterContext.Provider>
        );

        await act(async () => {
            userEvent.click(getByText('content'));

            return Promise.resolve();
        });

        expect(router.generate).toBeCalledWith('a_route');
        expect(router.redirect).toBeCalledWith('/an_url');
    });
});
