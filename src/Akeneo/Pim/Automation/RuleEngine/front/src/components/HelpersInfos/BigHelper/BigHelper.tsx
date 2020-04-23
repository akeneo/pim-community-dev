import React, {
  PropsWithChildren,
  isValidElement,
  Children,
  DetailedHTMLProps,
  AnchorHTMLAttributes,
} from 'react';
import defaultIllustrationUrl from '../../../assets/illustrations/api.svg';

const HelperTitle = ({ children }: PropsWithChildren<{}>) => <>{children}</>;

interface Props {
  illustrationUrl?: string;
}

const BigHelper = ({
  children,
  illustrationUrl = defaultIllustrationUrl,
}: PropsWithChildren<Props>) => {
  const titleChildren = Children.toArray(children).filter(
    child => isValidElement(child) && child.type === HelperTitle
  );
  const descriptionChildren = Children.toArray(children).filter(
    child => !isValidElement(child) || child.type !== HelperTitle
  );

  return (
    <div className='AknDescriptionHeader'>
      <div
        className='AknDescriptionHeader-icon'
        style={{ backgroundImage: `url('${illustrationUrl}')` }}></div>
      <div className='AknDescriptionHeader-title'>
        {titleChildren}
        <div className='AknDescriptionHeader-description'>
          {descriptionChildren}
        </div>
      </div>
    </div>
  );
};

const HelperLink = (
  props: DetailedHTMLProps<
    AnchorHTMLAttributes<HTMLAnchorElement>,
    HTMLAnchorElement
  >
) => <a {...props} className='AknDescriptionHeader-link' />;

export { HelperLink, HelperTitle, BigHelper };
