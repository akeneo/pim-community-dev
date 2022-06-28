import React, {ImgHTMLAttributes} from 'react';
import logoImage from '../assets/images/onboarderlogo.svg';

const OnboarderLogo = ({...rest}: ImgHTMLAttributes<HTMLImageElement>) => {
    return <img src={logoImage} alt="Onboarder logo" {...rest} />;
};

export {OnboarderLogo};
