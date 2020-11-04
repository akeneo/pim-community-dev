import React from 'react';
import styled from 'styled-components';

const SpanSrOnly = styled.span`
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
`;

const CrossLink: React.FC<React.AnchorHTMLAttributes<HTMLAnchorElement>> = ({
  children,
  href,
  onClick,
  ...rest
}) => {
  return (
    <a className='AknFullPage-cancel' href={href} onClick={onClick} {...rest}>
      <SpanSrOnly>{children}</SpanSrOnly>
    </a>
  );
};

export {CrossLink};
