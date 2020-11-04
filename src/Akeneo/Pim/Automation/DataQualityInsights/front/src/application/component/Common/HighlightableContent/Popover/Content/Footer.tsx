import React, {FC} from 'react';

type FooterProps = {};

const Footer: FC<FooterProps> = ({children}) => {
  return <footer>{children}</footer>;
};

export default Footer;
