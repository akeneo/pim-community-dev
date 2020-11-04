import {useLayoutEffect, useState} from 'react';

type HighlightState = {
  position: Position;
  dimension: Dimension;
};

type Position = {
  top: number;
  left: number;
};

type Dimension = {
  width: number;
  height: number;
};

const useHighlightState = (contentElement: Element, containerElement: Element): HighlightState => {
  const [position, setPosition] = useState<Position>({left: 0, top: 0});
  const [dimension, setDimension] = useState<Dimension>({width: 0, height: 0});

  useLayoutEffect(() => {
    const contentRect = contentElement.getBoundingClientRect();
    const containerRect = containerElement.getBoundingClientRect();

    setDimension({
      width: contentRect.width,
      height: contentRect.height,
    });

    setPosition({
      left: contentRect.x - containerRect.x,
      top: contentRect.y - containerRect.y,
    });
  }, [containerElement, contentElement]);

  return {
    position,
    dimension,
  };
};

export default useHighlightState;
