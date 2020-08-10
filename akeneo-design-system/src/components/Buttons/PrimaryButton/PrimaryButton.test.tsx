import React from 'react';
import 'jest-styled-components';
import { PrimaryButton } from './PrimaryButton';
import { render } from '@testing-library/react';

test('should render the component', () => {
    // Given
    const myLabel = 'Click Here';
    // When
    const { container } = render(<PrimaryButton>{myLabel}</PrimaryButton>);
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

        .c1 {
          color: white;
          background-color: #67B373;
        }

        .c1:hover {
          background-color: #528f5c;
        }

        .c1:active {
          background-color: #3d6b45;
        }

        .c1:focus {
          border-color: blue;
        }

        .c1:disabled {
          background-color: #c2e1c7;
        }

        <button
          class="c0 c1"
          role="button"
        >
          Click Here
        </button>
    `);
});
