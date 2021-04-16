import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Entities from '../../static/illustrations/Entities.svg';

const EntitiesIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Entities} />
  </svg>
);

export {EntitiesIllustration};
