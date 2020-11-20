import React from 'react';
import {BooleanInput} from './BooleanInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders default component', () => {
  render(<BooleanInput yesLabel={'Yes'} noLabel={'No'} readOnly={false} value={true} />);

  expect(screen.getByText('Yes')).toBeInTheDocument();
  expect(screen.getByText('No')).toBeInTheDocument();
});

test('it displays custom labels', () => {
  render(
    <BooleanInput
      readOnly={false}
      value={true}
      clearable={true}
      noLabel="Non"
      yesLabel="Oui"
      clearLabel="Effacer la valeur"
    />
  );

  expect(screen.getByText('Oui')).toBeInTheDocument();
  expect(screen.getByText('Non')).toBeInTheDocument();
  expect(screen.getByText('Effacer la valeur')).toBeInTheDocument();
});

test('it does not allow clear if this is readonly', () => {
  render(
    <BooleanInput
      yesLabel={'Yes'}
      noLabel={'No'}
      clearLabel={'Clear value'}
      value={true}
      clearable={true}
      readOnly={true}
    />
  );

  expect(screen.queryByText('Clear value')).not.toBeInTheDocument();
});

test('it does not allow clear if there is no value', () => {
  render(
    <BooleanInput
      yesLabel={'Yes'}
      noLabel={'No'}
      clearLabel={'Clear value'}
      readOnly={false}
      value={null}
      clearable={true}
    />
  );

  expect(screen.queryByText('Clear value')).not.toBeInTheDocument();
});

test('it executes callbacks on buttons', () => {
  const onChange = jest.fn();
  render(
    <BooleanInput
      yesLabel={'Yes'}
      noLabel={'No'}
      clearLabel={'Clear value'}
      readOnly={false}
      value={false}
      onChange={onChange}
      clearable={true}
    />
  );

  fireEvent.click(screen.getByText('Yes'));
  fireEvent.click(screen.getByText('No'));
  fireEvent.click(screen.getByText('Clear value'));

  expect(onChange).toBeCalledTimes(3);
  expect(onChange).toBeCalledWith(true);
  expect(onChange).toBeCalledWith(false);
  expect(onChange).lastCalledWith(null);
});

test('it does not call callback if readonly', () => {
  const onChange = jest.fn();
  render(<BooleanInput yesLabel={'Yes'} noLabel={'No'} value={false} onChange={onChange} readOnly={true} />);

  fireEvent.click(screen.getByText('Yes'));

  expect(onChange).not.toBeCalled();
});

test('it does not call callback if there is no callback', () => {
  const onChange = jest.fn();
  render(<BooleanInput yesLabel={'Yes'} noLabel={'No'} readOnly={false} value={false} />);

  fireEvent.click(screen.getByText('Yes'));

  expect(onChange).not.toBeCalled();
});

test('BooleanInput supports forwardRef', () => {
  const ref = {current: null};

  render(<BooleanInput yesLabel={'Yes'} noLabel={'No'} readOnly={false} value={true} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('BooleanInput supports ...rest props', () => {
  const {container} = render(
    <BooleanInput yesLabel={'Yes'} noLabel={'No'} readOnly={false} value={true} data-my-attribute="my_value" />
  );
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
