import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import News from '../../static/illustrations/News.svg';

const NewsIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={News} />
  </svg>
);

export {NewsIllustration};
