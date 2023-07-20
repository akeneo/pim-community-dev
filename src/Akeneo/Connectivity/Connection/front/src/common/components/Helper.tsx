import React, {AnchorHTMLAttributes, Children, DetailedHTMLProps, isValidElement, PropsWithChildren} from 'react';
import defaultIllustrationUrl from '../assets/illustrations/NewAPI.svg';

export const HelperTitle = ({children}: PropsWithChildren<{}>) => <>{children}</>;

interface Props {
    illustrationUrl?: string;
}

export const Helper = ({children, illustrationUrl = defaultIllustrationUrl}: PropsWithChildren<Props>) => {
    const titleChildren = Children.toArray(children).filter(
        child => isValidElement(child) && child.type === HelperTitle
    );
    const descriptionChildren = Children.toArray(children).filter(
        child => !isValidElement(child) || child.type !== HelperTitle
    );

    return (
        <div className='AknDescriptionHeader'>
            <div className='AknDescriptionHeader-icon' style={{backgroundImage: `url('${illustrationUrl}')`}}></div>
            <div className='AknDescriptionHeader-title'>
                {titleChildren}
                <div className='AknDescriptionHeader-description'>{descriptionChildren}</div>
            </div>
        </div>
    );
};

export const HelperLink = (props: DetailedHTMLProps<AnchorHTMLAttributes<HTMLAnchorElement>, HTMLAnchorElement>) => (
    <a {...(props as any)} className='AknDescriptionHeader-link' />
);
