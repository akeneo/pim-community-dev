import React from 'react';
import { ExampleButton } from "./ExampleButton";
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
            <ExampleButton disabled={false} onClick={onClick}>{myLabel}</ExampleButton>
        );
        // Then
        expect(getByText("Hello world")).toBeInTheDocument();
    });
});
