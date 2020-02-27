import React from 'react';
import { CoreButton } from "./CoreButton";
import {
    render, fireEvent
} from "@testing-library/react";

//https://github.com/testing-library/dom-testing-library/blob/master/src/events.js

describe("testing button", () => {
    test("should a button with the given children", () => {
        // Given
        const onClick = jest.fn();
        const myLabel = "Click Here";
        // When
        const { getByText } = render(
            <CoreButton onClick={onClick}>{myLabel}</CoreButton>
        );
        // Then
        expect(getByText("Click Here")).toBeInTheDocument();
    });
    test('should call the function callback when there is a left mouse click', () => {
        // Given
        const onClick = jest.fn();
        const myLabel = "Click Here";
        // When
        const { getByText } = render(
            <CoreButton onClick={onClick}>{myLabel}</CoreButton>
        );
        fireEvent.click(getByText("Click Here"), { bubbles: true, cancelable: true, button: 0 })
        // Then
        expect(onClick).toHaveBeenCalledTimes(1)
    });
});
