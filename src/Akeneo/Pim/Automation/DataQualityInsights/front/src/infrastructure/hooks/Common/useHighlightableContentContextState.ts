import {useCallback, useRef, useState} from 'react';
import {HighlightableContentContextState} from '../../../application/context/HighlightableContentContext';
import useElementContent from './useElementContent';
import useGetContentRef from './useGetContentRef';

const useHighlightableContentContextState = (element: HTMLElement): HighlightableContentContextState => {
  const mirrorRef = useRef<HTMLDivElement | null>(null);

  const {content, analyzableContent, refresh} = useElementContent(element);
  const handleGetContentRef = useGetContentRef(element, mirrorRef);

  const [isActive, setIsActive] = useState<boolean>(false);

  const handleDeactivate = useCallback(() => {
    setIsActive(false);
  }, [setIsActive]);

  const handleActivate = useCallback(() => {
    setIsActive(true);
  }, [setIsActive]);

  return {
    element,
    // @ts-ignore
    mirrorRef,
    content,
    analyzableContent,
    getContentRef: handleGetContentRef,
    isActive,
    activate: handleActivate,
    deactivate: handleDeactivate,
    refresh,
  };
};

export default useHighlightableContentContextState;
