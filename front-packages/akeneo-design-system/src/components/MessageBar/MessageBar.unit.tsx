import React from 'react';
import {MessageBar, AnimateMessageBar} from './MessageBar';
import {screen, act, render, fireEvent} from '../../storybook/test-util';
import {InfoIcon} from '../../icons';

jest.useFakeTimers();

test('it renders its children properly', () => {
  render(
    <>
      <MessageBar icon={<InfoIcon />} title="Only a title" onClose={jest.fn()} dismissTitle="Dismiss notification" />
      <MessageBar level="info" title="Title" onClose={jest.fn()} dismissTitle="Dismiss notification">
        MessageBar Info
      </MessageBar>
      <MessageBar level="success" title="Title" onClose={jest.fn()} dismissTitle="Dismiss notification">
        MessageBar Success
      </MessageBar>
      <MessageBar level="warning" title="Title" onClose={jest.fn()} dismissTitle="Dismiss notification">
        MessageBar Warning
      </MessageBar>
      <MessageBar level="error" title="Title" onClose={jest.fn()} dismissTitle="Dismiss notification">
        MessageBar Error
      </MessageBar>
    </>
  );

  expect(screen.getByText('Only a title')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Info')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Success')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Warning')).toBeInTheDocument();
  expect(screen.getByText('MessageBar Error')).toBeInTheDocument();
});

test('it calls the onClose handler when clicking on the close button', () => {
  const onClose = jest.fn();

  render(
    <MessageBar
      level="info"
      icon={<InfoIcon />}
      title="Title"
      onClose={onClose}
      dismissTitle="Dismiss the notification"
    >
      MessageBar Info
    </MessageBar>
  );

  fireEvent.click(screen.getByRole('button'));

  expect(onClose).toHaveBeenCalledTimes(1);
});

test('it stops the counter if we over the element', () => {
  const onClose = jest.fn();

  render(
    <MessageBar
      level="info"
      icon={<InfoIcon />}
      title="Title"
      onClose={onClose}
      dismissTitle="Dismiss the notification"
    >
      MessageBar Info
    </MessageBar>
  );

  fireEvent.mouseOver(screen.getByText('MessageBar Info'));

  act(() => {
    jest.runAllTimers();
  });

  expect(onClose).not.toHaveBeenCalled();
  fireEvent.mouseOut(screen.getByText('MessageBar Info'));

  act(() => {
    jest.runAllTimers();
  });
  expect(onClose).toHaveBeenCalledTimes(1);
});

test('it calls the onClose handler automatically after the appropriate duration', () => {
  const onClose = jest.fn();

  render(
    <MessageBar
      level="info"
      icon={<InfoIcon />}
      title="Title"
      onClose={onClose}
      dismissTitle="Dismiss the notification"
    >
      MessageBar Info
    </MessageBar>
  );

  act(() => {
    jest.runAllTimers();
  });

  expect(onClose).toHaveBeenCalledTimes(1);
});

test('It can animate a MessageBar', () => {
  const onClose = jest.fn();

  render(
    <AnimateMessageBar>
      <MessageBar
        level="info"
        icon={<InfoIcon />}
        title="Title"
        onClose={onClose}
        dismissTitle="Dismiss the notification"
      >
        MessageBar Info
      </MessageBar>
    </AnimateMessageBar>
  );

  fireEvent.click(screen.getByRole('button'));

  act(() => {
    jest.runAllTimers();
  });
  expect(onClose).toHaveBeenCalled();
});

test('It cannot animate something else than a MessageBar', () => {
  jest.spyOn(global.console, 'error').mockImplementation(jest.fn());

  expect(() => {
    render(
      <AnimateMessageBar>
        <div>Take me there</div>
      </AnimateMessageBar>
    );
  }).toThrow('Only MessageBar element can be passed to AnimateMessageBar');
});
