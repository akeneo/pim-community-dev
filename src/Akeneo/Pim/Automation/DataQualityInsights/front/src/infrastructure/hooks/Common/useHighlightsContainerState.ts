import {useLayoutEffect, useState} from 'react';
import {useGetEditorHighlightBoundingRect} from '../index';
import EditorElement from '../../../application/helper/EditorHighlight/EditorElement';
import useGetEditorScroll from '../EditorHighlight/useGetEditorScroll';

type Position = {
  top: number;
  left: number;
};

type Dimension = {
  width: number;
  height: number;
};

type ScrollPosition = {
  scrollLeft: number;
  scrollTop: number;
};

export type HighlightsContainerState = {
  position: Position;
  dimension: Dimension;
  scrollPosition: ScrollPosition;
};

const useHighlightsContainerState = (element: Element): HighlightsContainerState => {
  const {editorBoundingClientRect} = useGetEditorHighlightBoundingRect(element as EditorElement);
  const {editorScrollTop, editorScrollLeft} = useGetEditorScroll(element as EditorElement);

  const [position, setPosition] = useState<Position>({top: 0, left: 0});
  const [dimension, setDimension] = useState<Dimension>({width: 0, height: 0});
  const [scrollPosition, setScrollPosition] = useState<ScrollPosition>({scrollLeft: 0, scrollTop: 0});

  useLayoutEffect(() => {
    const rect = editorBoundingClientRect;

    setPosition({
      top: rect.y,
      left: rect.x,
    });

    setDimension({
      width: rect.width,
      height: rect.height,
    });
  }, [editorBoundingClientRect]);

  useLayoutEffect(() => {
    setScrollPosition({
      scrollLeft: editorScrollLeft,
      scrollTop: editorScrollTop,
    });
  }, [editorScrollTop, editorScrollLeft]);

  return {
    position,
    dimension,
    scrollPosition,
  };
};

export default useHighlightsContainerState;
