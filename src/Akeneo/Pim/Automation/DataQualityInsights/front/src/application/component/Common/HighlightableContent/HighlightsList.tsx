import React, {FC} from 'react';

import Highlight from './Highlight';
import {HighlightElement} from '../../../helper';

type HighlightsListProps = {
  highlights: HighlightElement[];
};

const HighlightsList: FC<HighlightsListProps> = ({highlights}) => {
  return (
    <>{highlights.length > 0 && highlights.map(highlight => <Highlight key={highlight.id} highlight={highlight} />)}</>
  );
};

export default HighlightsList;
