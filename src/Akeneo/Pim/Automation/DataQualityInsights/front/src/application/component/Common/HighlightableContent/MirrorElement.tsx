import React, {FC, useLayoutEffect} from 'react';
import useHighlightsContainerState from '../../../../infrastructure/hooks/Common/useHighlightsContainerState';
import {useHighlightableContentContext} from '../../../context/HighlightableContentContext';
import {getElementType} from '../../../helper/HighlightableContent';

type MirrorElementProps = {};

const MirrorElement: FC<MirrorElementProps> = () => {
  const {mirrorRef, content, element} = useHighlightableContentContext();
  const {scrollPosition, dimension} = useHighlightsContainerState(element as Element);
  const elementType = getElementType(element);

  useLayoutEffect(() => {
    const mirrorElement = mirrorRef.current;

    if (mirrorElement) {
      mirrorElement.scrollLeft = scrollPosition.scrollLeft;
      mirrorElement.scrollTop = scrollPosition.scrollTop;
    }
  }, [scrollPosition]);

  return (
    <div
      ref={mirrorRef}
      className={`AknEditorHighlight-cloned-editor AknEditorHighlight-cloned-editor--${elementType}`}
      aria-hidden={true}
      style={{
        width: dimension.width,
        height: dimension.height,
      }}
    >
      {content}
    </div>
  );
};

export default MirrorElement;
