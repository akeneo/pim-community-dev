import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Graph from '../../static/illustrations/Graph.svg';

const GraphIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Graph} />
  </svg>
);

export {GraphIllustration};
