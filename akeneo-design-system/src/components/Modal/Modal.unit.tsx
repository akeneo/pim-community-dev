import React from 'react';
import {Modal} from './Modal';
import {render} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(<Modal>Modal content</Modal>);

  expect(getByText('Modal content')).toBeInTheDocument();
});
