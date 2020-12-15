import { Notify, Router, Security, Translate, UserContext, ViewBuilder, Mediator } from './DependenciesProvider.type';
declare type DependenciesContextProps = {
    notify?: Notify;
    router?: Router;
    security?: Security;
    translate?: Translate;
    user?: UserContext;
    viewBuilder?: ViewBuilder;
    mediator?: Mediator;
};
declare const DependenciesContext: import("react").Context<DependenciesContextProps>;
export { DependenciesContext };
export type { DependenciesContextProps };
