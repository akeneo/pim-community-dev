import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Timezone from '../../static/illustrations/Timezone.svg';

const TimezoneIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Timezone} />
  </svg>
);

export {TimezoneIllustration};
