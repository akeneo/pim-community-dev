import React from 'react';
import {IconProps} from './IconProps';

const AttributeTextIcon = ({title, size = 24, color = 'currentColor', ...props}: IconProps) => (
  <svg viewBox="0 0 24 24" width={size} height={size} {...props}>
    {title && <title>{title}</title>}
    <defs>
      <path
        d="M15 14l-3.522-8L8 14h7zM2 22v-.537c.744-.087 1.303-.377 1.675-.87.372-.493 1.01-1.832 1.915-4.017L11.625 2h.566l7.208 16.838c.48 1.122.865 1.816 1.152 2.082.288.265.77.447 1.449.543V22h-7.364v-.537c.848-.077 1.395-.171 1.64-.282.245-.112.367-.385.367-.82 0-.145-.047-.401-.141-.769a8.51 8.51 0 00-.396-1.16l-1.201-2.857H7.329c-.754 1.944-1.204 3.13-1.35 3.56-.146.43-.22.772-.22 1.023 0 .503.199.85.594 1.044.245.116.707.203 1.386.261V22H2z"
        id="prefix__AttributeTextIcon"
      />
    </defs>
    <use fill={color} xlinkHref="#prefix__AttributeTextIcon" fillRule="evenodd" />
  </svg>
);

export {AttributeTextIcon};
