import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Import from '../../static/illustrations/Import.svg';

const ImportIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 243 177" {...props}>
    {title && <title>{title}</title>}
    <image href={Import} />
  </svg>
);

export {ImportIllustration};
