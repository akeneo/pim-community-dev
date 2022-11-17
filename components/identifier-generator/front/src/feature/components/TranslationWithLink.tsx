import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Link} from 'akeneo-design-system';

type TranslationWithLinkProps = {
  key: string;
  href: string;
  linkKey: string;
};

const TranslationWithLink: React.FC<TranslationWithLinkProps> = ({key, href, linkKey}) => {
  const translate = useTranslate();
  const translation = translate(key);
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
