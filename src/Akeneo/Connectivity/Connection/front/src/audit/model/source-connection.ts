import {FlowType} from '../../model/flow-type.enum';

export type Connection = {
    code: string;
    label: string;
    image: string | null;
    auditable: boolean;
    flowType: FlowType;
};

export type SourceConnection = Connection & {
    flowType: FlowType.DATA_SOURCE;
};
