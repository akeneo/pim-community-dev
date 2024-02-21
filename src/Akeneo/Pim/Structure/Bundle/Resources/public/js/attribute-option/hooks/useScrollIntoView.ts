import {RefObject, useLayoutEffect} from 'react';

export const useScrollIntoView = (ref: RefObject<HTMLElement>): void => {
  useLayoutEffect(() => {
    if (ref.current) {
      ref.current.scrollIntoView();
    }
  }, []);
};
