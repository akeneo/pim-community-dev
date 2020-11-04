import React, {FC} from 'react';
import useGetSpellcheckHighlights from '../../../../../infrastructure/hooks/Common/Spellcheck/useGetSpellcheckHighlights';
import HighlightableElement from '../HighlightableElement';
import DeactivateNativeSpellcheck from './DeactivateNativeSpellcheck';
import DeactivateGrammarlySpellcheck from './DeactivateGrammarlySpellcheck';
import {useSpellcheckContentContext} from '../../../../context/Spellcheck/SpellcheckContentContext';
import DeactivateNativeAutocomplete from './DeactivateNativeAutocomplete';

const DEFAULT_BASE_ID = 'spellcheck';

type SpellcheckElementProps = {
  baseId: string;
};

const SpellcheckElement: FC<SpellcheckElementProps> = ({children, baseId = DEFAULT_BASE_ID}) => {
  const {element, getContentRef, analysis} = useSpellcheckContentContext();
  const {highlights} = useGetSpellcheckHighlights(getContentRef, analysis);

  return (
    <HighlightableElement highlights={highlights} element={element} baseId={baseId}>
      <DeactivateNativeSpellcheck />
      <DeactivateGrammarlySpellcheck />
      <DeactivateNativeAutocomplete />
      {children}
    </HighlightableElement>
  );
};

export default SpellcheckElement;
