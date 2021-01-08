import React from 'react';
import {InputTag} from "./InputTag";
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '../../storybook/test-util';
import userEvent from "@testing-library/user-event";

test('it renders an empty input tag', () => {
    render(<InputTag/>);

    expect(screen.queryAllByTestId('tag')).toHaveLength(0);
});

test('it renders a list of tags', () => {
    render(<InputTag/>);

    userEvent.type(screen.getByTestId('tag-input'), 'gucci{space}samsung{space}')
    expect(screen.queryAllByTestId('tag')).toHaveLength(2);
});

test('it handle a copy pasted input list of tags', () => {
    render(<InputTag/>);

    userEvent.paste(screen.getByTestId('tag-input'), 'gucci samsung')
    expect(screen.queryAllByTestId('tag')).toHaveLength(2);
});
