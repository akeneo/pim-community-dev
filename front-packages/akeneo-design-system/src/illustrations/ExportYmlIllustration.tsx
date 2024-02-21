import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ExportYml from '../../static/illustrations/ExportYml.svg';

const ExportYmlIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ExportYml} />
  </svg>
);

export {ExportYmlIllustration};
