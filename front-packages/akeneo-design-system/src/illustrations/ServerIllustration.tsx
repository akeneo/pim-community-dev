import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Server from '../../static/illustrations/Server.svg';

const ServerIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Server} />
  </svg>
);

export {ServerIllustration};
