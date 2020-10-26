import React, {FC, useState} from 'react';

import useHighlightsContainerState from '../../../../infrastructure/hooks/Common/useHighlightsContainerState';
import {HighlightsContainerWithPortal} from './HighlightsContainer';
import {HighlightsContainerContextProvider} from '../../../context/HighlightsContainerContext';
import {useHighlightsContext} from '../../../context/HighlightsContext';

type HighlightableContentProps = {
  element: Element;
  baseId: string;
};

const HighlightableContent: FC<HighlightableContentProps> = ({element, baseId, children}) => {
  const {dimension, position, scrollPosition} = useHighlightsContainerState(element);
  const [root] = useState(document.body);
  const [containerId] = useState(`${baseId}-${element.id}`);
  const {highlights} = useHighlightsContext();

  return (
    <>
      <HighlightsContainerContextProvider
        dimension={dimension}
        position={position}
        scrollPosition={scrollPosition}
        element={element}
      >
        <HighlightsContainerWithPortal highlights={highlights} rootElement={root} containerId={containerId}>
          {children}
        </HighlightsContainerWithPortal>
      </HighlightsContainerContextProvider>
    </>
  );
};

export default HighlightableContent;
