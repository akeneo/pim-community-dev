import React, {SVGProps} from 'react';
import {useEffect, useState} from 'react';

type IllustrationProps = {
  title?: string;
  size?: number;
  className?: string;
} & SVGProps<SVGSVGElement>;

const BlankIllustration = ({title, size = 256}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256">
    {title && <title>{title}</title>}
  </svg>
);

const useIllustration = (illustrationName: string): React.FC<IllustrationProps> => {
  const [getIllustration, setIllustration] = useState<() => React.FC<IllustrationProps>>(() => () => BlankIllustration);
  useEffect(() => {
    import(
      /* webpackChunkName: "illustrations" */ `akeneo-design-system/lib/illustrations/${illustrationName}.js`
    ).then(({Illustration}: any) => {
      if (Illustration) {
        setIllustration(() => () => Illustration);
      }
    });
  }, []);

  return getIllustration();
};

export {useIllustration};
export type {IllustrationProps};
