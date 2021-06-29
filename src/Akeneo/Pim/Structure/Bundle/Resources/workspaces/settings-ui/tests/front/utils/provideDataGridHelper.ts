import React from 'react';

type Data = {
  id: number;
  label: string;
};

const aListOfData = (): Data[] => {
  return [
    {id: 1, label: 'item 1'},
    {id: 2, label: 'item 2'},
    {id: 3, label: 'item 3'},
    {id: 4, label: 'item 4'},
  ];
};

type Coordinates = {
  x: number;
  y: number;
};

const aDragEvent = (eventName: string, target: Element, coordinates?: Coordinates): React.DragEvent => {
  const event = document.createEvent('MouseEvent');
  event.initEvent(eventName);

  return {
    cancelable: false,
    defaultPrevented: false,
    eventPhase: 0,
    isTrusted: false,
    timeStamp: 0,
    type: '',
    isDefaultPrevented(): boolean {
      return false;
    },
    isPropagationStopped(): boolean {
      return false;
    },
    dataTransfer: {
      dropEffect: 'move',
      effectAllowed: 'move',
      files: {
        length: 0,
        item: jest.fn(),
      },
      items: {
        length: 0,
        add: jest.fn(),
        clear: jest.fn,
        item: jest.fn(),
        remove: jest.fn(),
      },
      types: [],
      clearData: jest.fn(),
      getData: jest.fn(),
      setData: jest.fn(),
      setDragImage: jest.fn(),
    },
    nativeEvent: event as DragEvent,
    preventDefault: jest.fn(),
    stopPropagation: jest.fn(),
    persist: jest.fn(),
    altKey: false,
    button: 0,
    buttons: 0,
    clientX: coordinates ? coordinates.x : 0,
    clientY: coordinates ? coordinates.y : 0,
    ctrlKey: false,
    getModifierState: jest.fn(),
    metaKey: false,
    movementX: 0,
    movementY: 0,
    pageX: 0,
    pageY: 0,
    relatedTarget: null,
    screenX: 0,
    screenY: 0,
    shiftKey: false,
    detail: 0,
    currentTarget: target,
    target: target,
    view: {
      styleMedia: {
        type: '',
        matchMedium: jest.fn(),
      },
      document,
    },
    bubbles: false,
  };
};

export {aListOfData, aDragEvent};
