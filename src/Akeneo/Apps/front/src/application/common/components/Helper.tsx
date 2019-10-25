import React, {PropsWithChildren, isValidElement, Children, DetailedHTMLProps, AnchorHTMLAttributes} from 'react';
import Api from '../assets/illustrations/Api.svg';

export const HelperTitle = ({children}: PropsWithChildren<{}>) => <>{children}</>;

interface Props {
    illustration?: string;
}

export const Helper = ({children, illustration = Api}: PropsWithChildren<Props>) => {
    const titleChildren = Children.toArray(children).filter(
        child => isValidElement(child) && child.type === HelperTitle
    );
    const descriptionChildren = Children.toArray(children).filter(
        child => !isValidElement(child) || child.type !== HelperTitle
    );

    return (
        <div className='AknDescriptionHeader'>
            <div className='AknDescriptionHeader-icon' style={{backgroundImage: `url('${illustration}')`}}></div>
            <div className='AknDescriptionHeader-title'>
                {titleChildren}
                <div className='AknDescriptionHeader-description'>{descriptionChildren}</div>
            </div>
        </div>
    );
};

export const HelperLink = (props: DetailedHTMLProps<AnchorHTMLAttributes<HTMLAnchorElement>, HTMLAnchorElement>) => (
    <a {...props} className='AknDescriptionHeader-link' />
);
