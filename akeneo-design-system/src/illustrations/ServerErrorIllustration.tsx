import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ServerError from '../../static/illustrations/ServerError.svg';

//TODO add brand
const ServerErrorIllustration = ({title, size = 500, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 500 250" {...props}>
    {title && <title>{title}</title>}
    <image href={ServerError} />
  </svg>
);

export {ServerErrorIllustration};
