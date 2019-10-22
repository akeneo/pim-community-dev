import * as React from 'react';
import {create} from 'react-test-renderer';
import {NoApp} from '../../../../../src/application/apps/components/NoApp';
import {mount} from 'enzyme';

describe('NoApp', () => {
    it('should render', () => {
        const component = create(<NoApp onCreate={() => undefined} />);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should call `onCreate` when the create app shortcut is clicked', () => {
        const handleCreate = jest.fn();

        const component = mount(<NoApp onCreate={handleCreate} />);

        component.find('a').simulate('click');
        expect(handleCreate).toBeCalled();
    });
});
