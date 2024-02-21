import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import DefaultPicture from '../../static/illustrations/DefaultPicture.svg';

const DefaultPictureIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={DefaultPicture} />
  </svg>
);

export {DefaultPictureIllustration};
