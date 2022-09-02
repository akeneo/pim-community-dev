import React, {ImgHTMLAttributes} from 'react';
import logoImage from '../assets/images/supplier_portal_logo.svg';

const SupplierPortalLogo = ({...rest}: ImgHTMLAttributes<HTMLImageElement>) => {
    return <img src={logoImage} alt="Supplier portal logo" {...rest} />;
};

export {SupplierPortalLogo};
