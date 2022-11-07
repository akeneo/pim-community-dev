import {RefObject, useEffect} from 'react';

function useOnFirstScreenDisplay(
  callback: () => void,
  ref: RefObject<Element>,
  rootRef: RefObject<Element>,
  rootMargin: string = '0px'
) {
  useEffect(() => {
    const currentRef = ref.current;
    /* istanbul ignore next */
    if (!currentRef) return;

    const observer = new IntersectionObserver(
      entries => {
        const lastEntry = entries[entries.length - 1];
        if (lastEntry.isIntersecting) {
          observer.unobserve(currentRef);
          callback();
        }
      },
      {rootMargin, root: rootRef.current}
    );

    observer.observe(currentRef);

    return () => {
      observer.unobserve(currentRef);
    };
  }, []);
}

export {useOnFirstScreenDisplay};
