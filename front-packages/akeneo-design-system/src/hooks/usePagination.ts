import {RefObject, useEffect} from 'react';

const usePagination = (
  containerRef: RefObject<HTMLElement>,
  lastOptionRef: RefObject<HTMLElement>,
  onNextPage: (() => void) | undefined,
  isVisible: boolean
) => {
  useEffect(() => {
    const containerElement = containerRef.current;
    const lastElement = lastOptionRef.current;
    if (
      undefined === onNextPage ||
      null === containerElement ||
      null === lastOptionRef.current ||
      null === lastElement
    ) {
      return;
    }

    const options = {
      root: containerElement,
      rootMargin: '0px 0px 100% 0px',
      threshold: 0,
    };

    const observer = new IntersectionObserver(entries => {
      const lastEntry = entries[entries.length - 1];
      if (lastEntry.isIntersecting) {
        onNextPage();
      }
    }, options);

    observer.observe(lastElement);

    return () => observer.unobserve(lastElement);
  }, [onNextPage, isVisible]);
};

export {usePagination};
