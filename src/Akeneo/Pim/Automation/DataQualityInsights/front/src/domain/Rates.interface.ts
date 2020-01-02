export default interface Rates {
  [channel: string]: {
    [locale: string]: string;
  };
}
