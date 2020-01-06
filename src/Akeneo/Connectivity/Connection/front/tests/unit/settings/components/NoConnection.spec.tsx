import {mount} from 'enzyme';
import * as React from 'react';
import {create} from 'react-test-renderer';
import {ThemeProvider} from 'styled-components';
import {NoConnection} from '../../../../src/settings/components/NoConnection';
import {theme} from '../../../../src/common/theme';

describe('NoConnection', () => {
    it('should render', () => {
        const component = create(
            <ThemeProvider theme={theme}>
                <NoConnection onCreate={() => undefined} />
            </ThemeProvider>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should call `onCreate` when the create connection shortcut is clicked', () => {
        const handleCreate = jest.fn();

        const component = mount(
            <ThemeProvider theme={theme}>
                <NoConnection onCreate={handleCreate} />
            </ThemeProvider>
        );

        component.find('a').simulate('click');
        expect(handleCreate).toBeCalled();
    });
});
