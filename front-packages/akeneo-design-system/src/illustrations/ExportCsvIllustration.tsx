import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ExportCsv from '../../static/illustrations/ExportCsv.svg';

const ExportCsvIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ExportCsv} />
  </svg>
);

export {ExportCsvIllustration};
