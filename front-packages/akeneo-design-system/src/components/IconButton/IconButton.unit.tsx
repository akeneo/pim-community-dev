import React from 'react';
import {IconButton} from './IconButton';
import {render, screen} from '../../storybook/test-util';
import {ActivityIcon} from '../../icons';

test('it renders its children properly', () => {
  render(
    <>
      <IconButton title="Icon button" icon={<ActivityIcon />} />
      <IconButton title="Icon button" size="small" icon={<ActivityIcon />} />
    </>
  );

  expect(screen.getAllByTitle('Icon button').length).toEqual(2);
});

test('it does not render other children than the given Icon', () => {
  render(
    //@ts-expect-error This other child should not be displayed
    <IconButton title="Icon button" icon={<ActivityIcon />}>
      Other child
    </IconButton>
  );

  expect(screen.queryByText('Other child')).not.toBeInTheDocument();
});

test('IconButton supports forwardRef', () => {
  const ref = {current: null};

  render(<IconButton title="Icon button" icon={<ActivityIcon />} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('IconButton supports ...rest props', () => {
  render(<IconButton title="Icon button" icon={<ActivityIcon />} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
