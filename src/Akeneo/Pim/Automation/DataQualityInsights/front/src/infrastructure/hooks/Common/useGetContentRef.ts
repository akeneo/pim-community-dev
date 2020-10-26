import {MutableRefObject, useCallback} from 'react';

import {isTextArea, isTextInput} from '../../../application/helper';
import EditorElement from '../../../application/helper/EditorHighlight/EditorElement';

type GetContentRefFnc = () => HTMLElement;

const useGetContentRef = (
  element: HTMLElement,
  mirrorRef: MutableRefObject<HTMLDivElement | null>
): GetContentRefFnc => {
  return useCallback(() => {
    if ((isTextInput(element as EditorElement) || isTextArea(element as EditorElement)) && mirrorRef.current !== null) {
      return mirrorRef.current;
    }
    return element;
  }, [element, mirrorRef]);
};

export default useGetContentRef;
