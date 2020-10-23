import React from 'react';
import {Modal} from './Modal';
import {render, screen} from '../../storybook/test-util';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  render(
    <Modal isOpen={true} onClose={jest.fn()}>
      Modal content
    </Modal>
  );

  expect(screen.getByText('Modal content')).toBeInTheDocument();
});

test('it does not display its children if it is closed', () => {
  render(
    <Modal isOpen={false} onClose={jest.fn()}>
      Modal content
    </Modal>
  );

  expect(screen.queryByText('Modal content')).not.toBeInTheDocument();
});
