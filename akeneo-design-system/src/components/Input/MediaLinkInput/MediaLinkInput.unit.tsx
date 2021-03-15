import React from 'react';
import {MediaLinkInput} from './MediaLinkInput';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(<MediaLinkInput>MediaLinkInput content</MediaLinkInput>);

  expect(screen.getByText('MediaLinkInput content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('MediaLinkInput supports forwardRef', () => {
  const ref = {current: null};

  render(<MediaLinkInput ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('MediaLinkInput supports ...rest props', () => {
  render(<MediaLinkInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
