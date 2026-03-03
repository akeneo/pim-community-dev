import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Measurement from '../../static/illustrations/Measurement.svg';

const MeasurementIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Measurement} />
  </svg>
);

export {MeasurementIllustration};
