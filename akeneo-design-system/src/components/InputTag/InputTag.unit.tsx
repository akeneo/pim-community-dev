import React from 'react';
import {InputTag} from "./InputTag";
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, render, screen} from '../../storybook/test-util';

test('it renders an empty input tag', () => {
    render(<InputTag/>);

    expect(screen.queryAllByTestId('tag')).toHaveLength(0);
});

test('it renders a list of tags', () => {
    const result = render(<InputTag/>);

    fireEvent.click(screen.getByTestId('tag-input'));
    fireEvent.keyUp(screen.getByTestId('tag-input'), {key: 'a', code: 'a'});
    fireEvent.keyUp(screen.getByTestId('tag-input'), {key: ' ', code: 'Space'});

    expect(screen.queryAllByTestId('tag')).toHaveLength(0);

    result.debug();
});
