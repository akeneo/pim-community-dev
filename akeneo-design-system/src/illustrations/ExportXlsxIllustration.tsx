import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ExportXlsx from '../../static/illustrations/ExportXlsx.svg';

const ExportXlsxIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ExportXlsx} />
  </svg>
);

export {ExportXlsxIllustration};
