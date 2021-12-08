import {FlowType} from '../flow-type.enum';

export type MonitoringSettings = {
    flowType: FlowType;
    auditable: boolean;
};
