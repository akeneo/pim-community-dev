import React from 'react';
import { CoreButton } from "./CoreButton";
import {
    render,
} from "@testing-library/react";

describe("testing button", () => {
    test("should do something", () => {
        // Given
        const onClick = jest.fn();
        const myLabel = "Hello world";
        // When
        const { getByText } = render(
            <CoreButton onClick={onClick}>{myLabel}</CoreButton>
        );
        // Then
        expect(getByText("Hello world")).toBeInTheDocument();
    });
});
