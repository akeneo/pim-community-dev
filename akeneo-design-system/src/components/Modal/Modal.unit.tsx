import React from 'react';
import {Modal, useInModal} from './Modal';
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

test('it calls the onClose handler when hitting the Escape key', () => {
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

const Component = () => {
  const isInModal = useInModal();
  return <span onClick={() => {}}>An element {isInModal ? 'inside' : 'outside'} of a modal</span>;
};

test('it provides a hook to know if an element is inside a modal', () => {
  const onClose = jest.fn();
  render(
    <Modal closeTitle="Close" onClose={onClose}>
      <Component />
    </Modal>
  );

  expect(screen.getByText('An element inside of a modal')).toBeInTheDocument();
  expect(screen.queryByText('An element outside of a modal')).not.toBeInTheDocument();
});

test('it provides a hook to know if an element is outside a modal', () => {
  const onClose = jest.fn();
  render(
    <>
      <Modal closeTitle="Close" onClose={onClose}></Modal>

      <Component />
    </>
  );

  expect(screen.queryByText('An element inside of a modal')).not.toBeInTheDocument();
  expect(screen.getByText('An element outside of a modal')).toBeInTheDocument();
});
