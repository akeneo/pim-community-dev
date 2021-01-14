import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Draft from '../../static/illustrations/Draft.svg';
import {BrandedPath} from '../theme';

const DraftIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Draft} />
    <BrandedPath d="M136.516 122.069c.142.417.265.7.302.795.01.01-.012-.005 0 0 0 0 0 .003 0 0 0 .002-.002 0-.002 0-.944-9.111 4.222-12.907 5.884-18.96.007-.09.025-.187.052-.297.035-.135.063-.275.083-.414.247-1.675-.44-3.624-1.74-5.653-.004-.012-.013-.022-.018-.033-.107-.17-.222-.337-.336-.506 0 0-.005-.003-.005-.007a23.814 23.814 0 00-.681-.931c-.024-.032-.047-.066-.072-.098-.068-.084-.137-.168-.202-.254l-.208-.262-.213-.255c-.073-.09-.147-.175-.222-.264-.05-.06-.105-.121-.16-.184-2.041-2.351-4.698-4.675-7.472-6.696 0 0-.021-.031 0 0 .367 3.429.414 6.858.089 9.599-.391 3.305-1.575 7.153-3.216 10.64-3.841-.314-7.766-1.204-10.83-2.511-2.558-1.093-5.491-2.818-8.295-4.864.032.285.066.566.099.85.01.085.022.169.033.254.026.203.05.404.082.607a28.515 28.515 0 00.134.887l.039.246c.042.256.086.51.133.764a31.696 31.696 0 00.179.954c0 .022.008.043.013.067.054.262.107.52.163.779.012.054.024.106.034.159.053.236.108.47.163.706.013.045.025.09.035.138.067.27.133.54.203.806a37.473 37.473 0 00.747 2.477c.004.01.008.019.009.029.456 1.323.975 2.542 1.554 3.609.086.154.172.307.26.455.004.006.004.013.009.018.08.136.16.269.24.396l.004.003.009.014c.087.139.176.276.267.405l.038.053c.002 0 0 .003.002.004.116.17.237.333.355.489.023.026.043.05.062.075.006.01.014.018.02.03a8.4 8.4 0 00.429.5c.099.113.203.218.307.322.044.044.09.084.134.125.066.062.13.12.196.179.049.043.1.08.152.123.06.052.127.102.19.152.05.038.1.073.154.109.066.048.13.093.198.138l.154.094.212.12c.049.027.1.052.147.077.082.038.163.073.246.109.038.014.08.036.123.05.125.052.25.093.377.128.085.026.157.052.226.079 6.083 1.606 12.005-.94 19.433 4.37" />
  </svg>
);

export {DraftIllustration};
