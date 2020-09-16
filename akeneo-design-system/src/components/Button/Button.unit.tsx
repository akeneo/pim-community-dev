import React from 'react';
import {fireEvent, render} from 'storybook/test-util';
import {Button} from './Button';

it('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button size="small" onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});
it('it calls onClick handler when user hit enter key on button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).toBeCalled();
});

it('it does not call onClick handler when user clicks on a disabled button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button disabled={true} ghost={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});
it('it does not call onClick handler when user hit enter key on button', () => {
  const onClick = jest.fn();
  const {getByText} = render(
    <Button disabled={true} onClick={onClick}>
      Hello
    </Button>
  );

  const button = getByText('Hello');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

// it('it does not call onChange handler when read-only', () => {
//   const onChange = jest.fn();
//   const {getByText} = render(<Button checked={true} readOnly={true} onChange={onChange} label="Checkbox" />);

//   const checkbox = getByText('Checkbox');
//   fireEvent.click(checkbox);

//   expect(onChange).not.toBeCalled();
// });

// it('it cannot be instantiated without handler when not readonly', () => {
//   jest.spyOn(console, 'error').mockImplementation(() => {
//     // do nothing.
//   });

//   expect(() => {
//     render(<Button checked={true} label="Checkbox" />);
//   }).toThrow('A Checkbox element expect an onChange attribute if not readOnly');
// });
