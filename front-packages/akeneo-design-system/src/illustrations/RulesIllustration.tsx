import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Rules from '../../static/illustrations/Rules.svg';

const RulesIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Rules} />
  </svg>
);

export {RulesIllustration};
