import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Link} from 'akeneo-design-system';

type TranslationWithLinkProps = {
  translationKey: string;
  href: string;
  linkKey: string;
};

const TranslationWithLink: React.FC<TranslationWithLinkProps> = ({translationKey, href, linkKey}) => {
  const translate = useTranslate();
  const translation = translate(translationKey);
  const linkTranslation = translate(linkKey);
  const [left, right] = translation.split('{{link}}');

  return (
    <>
      {left}
      <Link href={href} target={'_blank'}>
        {linkTranslation}
      </Link>
      {right}
    </>
  );
};

export {TranslationWithLink};
