import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ImportXlsx from '../../static/illustrations/ImportXlsx.svg';

const ImportXlsxIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ImportXlsx} />
  </svg>
);

export {ImportXlsxIllustration};
