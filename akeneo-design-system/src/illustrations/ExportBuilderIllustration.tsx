import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import ExportBuilder from '../../static/illustrations/ExportBuilder.svg';

const ExportBuilderIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={ExportBuilder} />
  </svg>
);

export {ExportBuilderIllustration};
