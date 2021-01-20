declare type LabelCollection = {
    [localeCode: string]: string;
};
declare const isLabelCollection: (labelCollection: any) => labelCollection is LabelCollection;
export { isLabelCollection };
export type { LabelCollection };
