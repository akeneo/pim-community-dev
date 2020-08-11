import React from 'react';
import 'jest-styled-components';

import {CoreButton} from './CoreButton';
import {render, fireEvent} from '@testing-library/react';

describe('testing button', () => {
  test('should display a button with the given children', () => {
    // Given
    const myLabel = 'Click Here';
    // When
    const {getByText} = render(<CoreButton>{myLabel}</CoreButton>);
    // Then
    expect(getByText('Click Here')).toBeInTheDocument();
  });
  test('should call the function callback when left mouse is clicked', () => {
    // Given
    const onClick = jest.fn();
    const myLabel = 'Click Here';
    // When
    const {getByText} = render(<CoreButton onClick={onClick}>{myLabel}</CoreButton>);
    fireEvent.click(getByText('Click Here'), {button: 0});
    // Then
    expect(onClick).toHaveBeenCalledTimes(1);
  });
  test('should call the function callback when keyboard enter is pressed down', () => {
    // Given
    const onKeyDown = jest.fn();
    const myLabel = 'Click Here';
    // When
    const {getByText} = render(<CoreButton onKeyDown={onKeyDown}>{myLabel}</CoreButton>);
    fireEvent.keyDown(getByText('Click Here'), {keyCode: 13});
    // Then
    expect(onKeyDown).toHaveBeenCalledTimes(1);
  });
  test('should call the function callback when keyboard space is pressed down', () => {
    // Given
    const onKeyDown = jest.fn();
    const myLabel = 'Click Here';
    // When
    const {getByText} = render(<CoreButton onKeyDown={onKeyDown}>{myLabel}</CoreButton>);
    fireEvent.keyDown(getByText('Click Here'), {keyCode: 32});
    // Then
    expect(onKeyDown).toHaveBeenCalledTimes(1);
  });
  test('should not call the function callback when other keyboard is pressed down', () => {
    // Given
    const onKeyDown = jest.fn();
    const myLabel = 'Click Here';
    // When
    const {getByText} = render(<CoreButton onKeyDown={onKeyDown}>{myLabel}</CoreButton>);
    fireEvent.keyDown(getByText('Click Here'), {keyCode: 8});
    // Then
    expect(onKeyDown).toHaveBeenCalledTimes(0);
  });
  test('should assign a dom button to the passed ref', () => {
    // Given
    const myLabel = 'Click Here';
    const ref = React.createRef<HTMLButtonElement>();
    // When
    const {container} = render(<CoreButton ref={ref}>{myLabel}</CoreButton>);
    // Then
    expect(ref.current).toEqual(container.firstChild);
  });
  test('should render the component, not disabled and in large mode', () => {
    // Given
    const myLabel = 'Click Here';
    // When
    const {container} = render(<CoreButton>{myLabel}</CoreButton>);
    // Then
    expect(container.firstChild).toMatchInlineSnapshot(`
            .c0 {
              border-radius: 16px;
              cursor: pointer;
              font-size: 13px;
              font-weight: 400;
              height: 32px;
              line-height: 32px;
              padding: 0 15px;
              text-transform: uppercase;
            }

            .c0:disabled {
              cursor: not-allowed;
            }

            <button
              class="c0"
              role="button"
            >
              Click Here
            </button>
        `);
  });
  test('should render the component in small mode', () => {
    // Given
    const myLabel = 'Click Here';
    // When
    const {container} = render(<CoreButton sizeMode='small'>{myLabel}</CoreButton>);
    // Then
    expect(container.firstChild).toHaveStyleRule('height', '20px');
    expect(container.firstChild).toHaveStyleRule('line-height', '20px');
  });
});
