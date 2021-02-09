import React from 'react';
import {Modal} from './Modal';
import {fireEvent, render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Modal closeTitle="Close" onClose={jest.fn()}>
      Modal content
    </Modal>
  );

  expect(screen.getByText('Modal content')).toBeInTheDocument();
});

test('it renders its exposed subcomponent `BottomButtons` properly', () => {
  render(
    <Modal closeTitle="Close" onClose={jest.fn()}>
      <Modal.BottomButtons>Buttons</Modal.BottomButtons>
    </Modal>
  );

  expect(screen.getByText('Buttons')).toBeInTheDocument();
});

test('it calls the onClose handler when clicking on the close button', () => {
  const onClose = jest.fn();

  render(
    <Modal closeTitle="Close" onClose={onClose}>
      Modal content
    </Modal>
  );

  fireEvent.click(screen.getByTitle('Close'));

  expect(onClose).toBeCalledTimes(1);
});

test('it calls the onClose handler when hitting the Escape key and onEscape is not given', () => {
  const onClose = jest.fn();

  render(
    <Modal closeTitle="Close" onClose={onClose}>
      <Modal.SectionTitle>With a section Title</Modal.SectionTitle>
      Modal content
    </Modal>
  );

  fireEvent.keyDown(document, {key: 'Escape', code: 'Escape'});

  expect(onClose).toBeCalledTimes(1);
});

test('it calls the onEscape handler when hitting the Escape key', () => {
  const onClose = jest.fn();
  const onEscape = jest.fn();

  render(
    <Modal closeTitle="Close" onClose={onClose} onEscape={onEscape}>
      <Modal.SectionTitle>With a section Title</Modal.SectionTitle>
      Modal content
    </Modal>
  );

  fireEvent.keyDown(document, {key: 'Escape', code: 'Escape'});

  expect(onEscape).toBeCalledTimes(1);
  expect(onClose).not.toBeCalled();
});
