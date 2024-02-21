import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Image} from './Image';

test('it renders an image properly', () => {
  render(<Image src="image.png" alt="my image" />);

  expect(screen.getByAltText('my image')).toBeInTheDocument();
});

test('it renders stacked image', () => {
  render(<Image isStacked src="image.png" alt="my image" />);

  expect(screen.getByAltText('my image')).toBeInTheDocument();
});

test('Image supports forwardRef', () => {
  const ref = {current: null};
  render(<Image src="image.png" alt="my image" ref={ref} />);

  expect(ref.current).not.toBe(null);
});

test('Image supports ...rest props', () => {
  render(<Image src="image.png" alt="my image" data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
