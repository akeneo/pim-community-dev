import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ImportYml from '../../static/illustrations/ImportYml.svg';

const ImportYmlIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ImportYml} />
  </svg>
);

export {ImportYmlIllustration};
