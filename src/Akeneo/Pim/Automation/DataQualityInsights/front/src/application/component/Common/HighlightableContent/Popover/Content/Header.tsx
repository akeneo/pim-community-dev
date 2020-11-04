import React, {FC} from 'react';

type HeaderProps = {};

const Header: FC<HeaderProps> = ({children}) => {
  return <header>{children}</header>;
};

export default Header;
