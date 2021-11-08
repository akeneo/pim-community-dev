import React, {FC, useEffect} from 'react';
import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';

type ActiveHighlightsOnIntersectionProps = {
  options?: IntersectionObserverInit;
};

const ActiveHighlightsOnIntersection: FC<ActiveHighlightsOnIntersectionProps> = ({options}) => {
  const {element, activate, deactivate} = useHighlightableContentContext();

  useEffect(() => {
    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          activate();
        } else {
          deactivate();
        }
      });
    }, options);

    if (element !== null) {
      observer.observe(element);
    }

    return () => {
      if (element !== null) {
        observer.unobserve(element);
      }
      observer.disconnect();
    };
  }, [element, activate, deactivate]);

  return <></>;
};

export default ActiveHighlightsOnIntersection;
