declare module 'react-country-flag' {
  export interface ReactCountryFlagProps<T> extends React.DetailedHTMLProps<React.LabelHTMLAttributes<T>, T> {
    cdnSuffix?: string;
    /** @default 'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/'' */
    cdnUrl?: string;
    countryCode: string;
    /** @default false */
    svg?: boolean;
    style?: React.CSSProperties;
  }

  /**
   * React component for emoji/svg country flags
   */
  declare const ReactCountryFlag: React.FC<ReactCountryFlagProps<HTMLSpanElement | HTMLImageElement>>;

  export default ReactCountryFlag;
}
