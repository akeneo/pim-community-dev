import {useLayoutEffect, useState} from 'react';

export type ElementsList = {
  [key: string]: HTMLElement;
};

export type SpellcheckLabelsListState = {
  elements: ElementsList;
};

const uuidV5 = require('uuid/v5');

const SPELLCHECK_ELEMENTS_UUID_NAMESPACE = 'b48d69a5-a131-45af-8f9a-9400447e3b4c';

const useSpellcheckLabelsListState = (renderingId: number): SpellcheckLabelsListState => {
  const [elements, setElements] = useState<ElementsList>({});

  useLayoutEffect(() => {
    const list: ElementsList = {};
    const nodes = document.querySelectorAll<HTMLElement>('input[id^="pim_enrich_attribute_form_label_"]');

    nodes.forEach((element, key) => {
      const identifier: string = uuidV5(`element-${key}`, SPELLCHECK_ELEMENTS_UUID_NAMESPACE);
      list[identifier] = element;
    });

    setElements(list);
  }, [renderingId]);

  return {
    elements,
  };
};

export default useSpellcheckLabelsListState;
