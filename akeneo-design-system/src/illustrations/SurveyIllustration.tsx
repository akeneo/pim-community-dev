import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Survey from '../../static/illustrations/Survey.svg';

const SurveyIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Survey} />
  </svg>
);

export {SurveyIllustration};
