import React, {Component} from 'react';
import {PageContent, RuntimeError} from '../../common/components';
import {NotFoundError, UnauthorizedError} from '../services/fetch';

class ErrorBoundary extends Component<unknown, {error?: Error}> {
    constructor(props: unknown) {
        super(props);
        this.state = {};
    }

    static getDerivedStateFromError(error: Error) {
        if (error instanceof UnauthorizedError) {
            // Reload the page to display the login form.
            window.location.reload();
        }

        return {error};
    }

    render() {
        if (this.state.error) {
            return (
                <PageContent>
                    {/* TODO Create NotFoundError component */}
                    {this.state.error instanceof NotFoundError ? <>NotFoundError</> : <RuntimeError />}
                </PageContent>
            );
        }

        return this.props.children;
    }
}

export {ErrorBoundary};
