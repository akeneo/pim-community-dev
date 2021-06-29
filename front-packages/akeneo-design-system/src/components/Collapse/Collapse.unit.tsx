import React from 'react';
import userEvent from '@testing-library/user-event';
import {Collapse} from './Collapse';
import {render, screen} from '../../storybook/test-util';
import {Badge, Pill} from '../../components';

jest.useFakeTimers();

test('it renders its children along with its label', () => {
  render(
    <Collapse
      isOpen={true}
      onCollapse={jest.fn()}
      collapseButtonLabel="Collapse"
      label={
        <>
          Hello <Badge>42</Badge> <Pill level="danger" />
        </>
      }
    >
      Collapse content
    </Collapse>
  );

  jest.runAllTimers();

  expect(screen.getByText('Hello')).toBeInTheDocument();
  expect(screen.getByText('42')).toBeInTheDocument();
  expect(screen.getByRole('alert')).toBeInTheDocument();
  expect(screen.getByText('Collapse content')).toBeInTheDocument();
});

test('it calls the onCollapse handler when hitting the collapse button', () => {
  const handleCollapse = jest.fn();

  render(
    <Collapse isOpen={false} onCollapse={handleCollapse} collapseButtonLabel="Collapse" label="Closed Collapse">
      Collapse content
    </Collapse>
  );

  userEvent.click(screen.getByTitle('Collapse'));

  expect(handleCollapse).toHaveBeenCalledWith(true);
});

test('Collapse supports forwardRef', () => {
  const ref = {current: null};

  render(<Collapse isOpen={true} onCollapse={jest.fn()} collapseButtonLabel="Collapse" label="Hello" ref={ref} />);

  expect(ref.current).not.toBe(null);
});

test('Collapse supports ...rest props', () => {
  render(
    <Collapse
      isOpen={true}
      onCollapse={jest.fn()}
      collapseButtonLabel="Collapse"
      label="Hello"
      data-testid="my_value"
    />
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
