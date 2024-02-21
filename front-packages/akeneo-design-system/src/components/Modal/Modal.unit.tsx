import React from 'react';
import {Modal, useInModal} from './Modal';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Table} from '../Table/Table';
import userEvent from '@testing-library/user-event';
import {Button} from '../Button/Button';

test('it renders its children properly', () => {
  render(
    <Modal closeTitle="Close" onClose={jest.fn()}>
      Modal content
    </Modal>
  );

  expect(screen.getByText('Modal content')).toBeInTheDocument();
});

test('it renders its exposed subcomponent `BottomButtons`, `TopLeftButtons` and `TopRightButtons` properly', () => {
  render(
    <Modal closeTitle="Close" onClose={jest.fn()}>
      <Modal.BottomButtons>Bottom Button</Modal.BottomButtons>
      <Modal.TopLeftButtons>TopLeft Button</Modal.TopLeftButtons>
      <Modal.TopRightButtons>TopRight Button</Modal.TopRightButtons>
    </Modal>
  );

  expect(screen.getByText('Bottom Button')).toBeInTheDocument();
  expect(screen.getByText('TopLeft Button')).toBeInTheDocument();
  expect(screen.getByText('TopRight Button')).toBeInTheDocument();
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

test('it does not forward click on parent node by default', () => {
  const handleRowClick = jest.fn();
  const handleButtonClick = jest.fn();

  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell onClick={handleRowClick}>
            <Modal closeTitle="Close" onClose={jest.fn()}>
              Modal content
              <Button onClick={handleButtonClick}>Button</Button>
            </Modal>
          </Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  userEvent.click(screen.getByText('Button'));

  expect(handleButtonClick).toBeCalledTimes(1);
  expect(handleRowClick).toBeCalledTimes(0);
});

const Component = () => {
  const isInModal = useInModal();
  return <span>An element {isInModal ? 'inside' : 'outside'} of a modal</span>;
};

test('it provides a hook to know if an element is inside a modal', () => {
  render(
    <Modal closeTitle="Close" onClose={jest.fn()}>
      <Component />
    </Modal>
  );

  expect(screen.getByText('An element inside of a modal')).toBeInTheDocument();
  expect(screen.queryByText('An element outside of a modal')).not.toBeInTheDocument();
});

test('it provides a hook to know if an element is outside a modal', () => {
  render(
    <>
      <Modal closeTitle="Close" onClose={jest.fn()}></Modal>
      <Component />
    </>
  );

  expect(screen.queryByText('An element inside of a modal')).not.toBeInTheDocument();
  expect(screen.getByText('An element outside of a modal')).toBeInTheDocument();
});
