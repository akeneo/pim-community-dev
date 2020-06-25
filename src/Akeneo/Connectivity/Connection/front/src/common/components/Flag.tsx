import React, {FC} from 'react';

type Props = {
    locale: string;
};

const Flag: FC<Props> = ({locale}) => {
    const region = locale.split('_')[locale.split('_').length - 1];

    return <i className={`flag flag-${region.toLowerCase()}`} />;
};

export {Flag};
