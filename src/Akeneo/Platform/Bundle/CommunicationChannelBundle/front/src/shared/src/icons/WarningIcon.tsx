import * as React from 'react';
import {IconProps} from '../icons';
import {useAkeneoTheme} from '../hooks';

const WarningIcon = ({title = 'Warning', color, size = 24, ...props}: IconProps) => (
  <svg width={size} height={size} viewBox="0 0 24 24" {...props}>
    <title>{title}</title>
    <g stroke="none" strokeWidth="1" fill="none" fillRule="evenodd" strokeLinecap="round" strokeLinejoin="round">
      <g stroke={color || useAkeneoTheme().color.grey100}>
        <path d="M13.7888544,3.57770876 L20.5527864,17.1055728 C21.0467649,18.0935298 20.6463162,19.2948759 19.6583592,19.7888544 C19.3806483,19.9277098 19.0744222,20 18.763932,20 L5.23606798,20 C4.13149848,20 3.23606798,19.1045695 3.23606798,18 C3.23606798,17.6895098 3.30835816,17.3832837 3.4472136,17.1055728 L10.2111456,3.57770876 C10.7051241,2.58975177 11.9064702,2.18930308 12.8944272,2.68328157 C13.281482,2.87680898 13.595327,3.19065396 13.7888544,3.57770876 Z M12.0002191,6.5 L12,13.5 M12.0002191,16 L12,17" />
      </g>
    </g>
  </svg>
);

export {WarningIcon};
