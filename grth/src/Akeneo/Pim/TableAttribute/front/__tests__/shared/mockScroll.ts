type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

const mockScroll = () => {
  let entryCallback: EntryCallback | undefined = undefined;
  const intersectionObserverMock = (callback: EntryCallback) => ({
    observe: jest.fn(() => (entryCallback = callback)),
    unobserve: jest.fn(),
  });
  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

  return () => {
    entryCallback?.([{isIntersecting: true}]);
  };
};

export {mockScroll};
