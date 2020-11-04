import React, {FC} from 'react';

import {HighlightsContextProvider} from '../../../context/HighlightsContext';
import HighlightableContent from './HighlightableContent';
import {HighlightElement} from '../../../helper';

type HighlightableElementProps = {
  element: HTMLElement | null;
  highlights: HighlightElement[];
  baseId: string;
};

const HighlightableElement: FC<HighlightableElementProps> = ({children, element, highlights, baseId}) => {
  return (
    <>
      {element !== null && (
        <HighlightsContextProvider highlights={highlights}>
          <HighlightableContent element={element} baseId={baseId}>
            {children}
          </HighlightableContent>
        </HighlightsContextProvider>
      )}
    </>
  );
};

export default HighlightableElement;
