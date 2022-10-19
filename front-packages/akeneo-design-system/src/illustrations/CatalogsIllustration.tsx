import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Catalogs from '../../static/illustrations/Catalogs.svg';

const CatalogsIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Catalogs} />
  </svg>
);

export {CatalogsIllustration};
