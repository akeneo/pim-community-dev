import {useLayoutEffect, useState} from 'react';

import {createHighlight, HighlightElement, MistakeElement} from '../../../../application/helper';
import EditorElement from '../../../../application/helper/EditorHighlight/EditorElement';
import {useMountedState} from '../useMountedState';

const uuidV5 = require('uuid/v5');

const HIGHLIGHT_UUID_NAMESPACE = '4e34f5c2-d1b0-4cf2-96c9-dca6b95e695e';

const generateHighlights = async (containerId: string, mistakes: MistakeElement[], element: Element) => {
  return new Promise<HighlightElement[]>(resolve => {
    const highlights = mistakes.map(mistake => {
      const identifier = uuidV5(`${mistake.text}-${mistake.globalOffset}`, containerId);
      return createHighlight(identifier, mistake, element as EditorElement);
    });

    return resolve(highlights);
  });
};

const useGetSpellcheckHighlights = (getContentRef: () => HTMLElement | null, analysis: MistakeElement[]) => {
  const [highlights, setHighlights] = useState<HighlightElement[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const {isMounted} = useMountedState();

  useLayoutEffect(() => {
    if (analysis.length > 0) {
      const element = getContentRef();

      if (element !== null) {
        (async () => {
          setIsLoading(true);
          const result = await generateHighlights(uuidV5(element.id, HIGHLIGHT_UUID_NAMESPACE), analysis, element);

          if (isMounted()) {
            setHighlights(result);
          }

          setIsLoading(false);
        })();
      } else {
        setHighlights([]);
      }
    } else {
      setHighlights([]);
    }
  }, [getContentRef, analysis]);

  return {
    highlights,
    isLoading,
  };
};

export default useGetSpellcheckHighlights;
