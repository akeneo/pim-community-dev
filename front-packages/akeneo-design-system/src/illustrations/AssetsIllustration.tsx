import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Assets from '../../static/illustrations/Assets.svg';

const AssetsIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Assets} />
  </svg>
);

export {AssetsIllustration};
