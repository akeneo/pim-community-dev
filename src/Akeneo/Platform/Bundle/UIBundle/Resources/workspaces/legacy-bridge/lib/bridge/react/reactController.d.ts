/// <reference types="jquery" />
declare const BaseController: any;
declare abstract class ReactController extends BaseController {
    abstract reactElementToMount(): JSX.Element;
    abstract routeGuardToUnmount(): RegExp;
    initialize(): any;
    renderRoute(): JQuery.Deferred<any, any, any>;
    remove(): any;
    private handleRouteChange;
}
export { ReactController };
