import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Settings from '../../static/illustrations/Settings.svg';

const SettingsIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Settings} />
  </svg>
);

export {SettingsIllustration};
