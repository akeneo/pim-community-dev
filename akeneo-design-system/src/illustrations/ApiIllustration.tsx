import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Api from '../../static/illustrations/Api.svg';

const ApiIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Api} />
  </svg>
);

export {ApiIllustration};
