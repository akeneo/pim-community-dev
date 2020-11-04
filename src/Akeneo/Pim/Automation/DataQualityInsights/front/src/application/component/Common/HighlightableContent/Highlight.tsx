import React, {FC, useLayoutEffect, useState} from 'react';

import LegacyHighlight from '../../ProductEditForm/TabContent/Attributes/EditorHighlight/Highlight';
import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';
import {HighlightElement} from '../../../helper';
import {useHighlightsContainerContext} from '../../../context/HighlightsContainerContext';

type HighlightProps = {
  highlight: HighlightElement;
};

const buildElementRect = (left: number, top: number, width: number, height: number): DOMRect => {
  return {
    x: left,
    y: top,
    left: left,
    top: top,
    width: width,
    height: height,
    bottom: top + height,
    right: left + width,
    toJSON: () => ({}),
  };
};

const Highlight: FC<HighlightProps> = ({highlight}) => {
  const {content, element} = useHighlightableContentContext();
  const {position, dimension} = useHighlightsContainerContext();

  const [elementRect, setElementRect] = useState<DOMRect>(buildElementRect(0, 0, 0, 0));

  useLayoutEffect(() => {
    if (element !== null) {
      setElementRect(buildElementRect(position.left, position.top, dimension.width, dimension.height));
    }
  }, [position, dimension]);

  return <LegacyHighlight key={highlight.id} highlight={highlight} editorRect={elementRect} content={content} />;
};

export default Highlight;
