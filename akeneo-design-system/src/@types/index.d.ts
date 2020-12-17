declare module 'react-country-flag' {
  export interface ReactCountryFlagProps<T> extends React.DetailedHTMLProps<React.LabelHTMLAttributes<T>, T> {
    cdnSuffix?: string;
    cdnUrl?: string;
    countryCode: string;
    svg?: boolean;
    style?: React.CSSProperties;
  }
  const ReactCountryFlag: React.FC<ReactCountryFlagProps<HTMLSpanElement | HTMLImageElement>> = ({
    cdnSuffix = 'svg',
    cdnUrl = 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/',
    svg = false,
    style = {},
    countryCode,
  }) => React.ReactNode; // return should be HTMLImageElement or HTMLSpanElement but close enough for me.
  export default ReactCountryFlag;
}
