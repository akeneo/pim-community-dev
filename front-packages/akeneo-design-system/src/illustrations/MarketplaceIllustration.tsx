import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Marketplace from '../../static/illustrations/Marketplace.svg';

const MarketplaceIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Marketplace} />
  </svg>
);

export {MarketplaceIllustration};
