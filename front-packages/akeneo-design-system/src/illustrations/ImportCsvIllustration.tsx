import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ImportCsv from '../../static/illustrations/ImportCsv.svg';

const ImportCsvIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ImportCsv} />
  </svg>
);

export {ImportCsvIllustration};
