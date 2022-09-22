# CircleCi: Supplier Portal CI/CD

This page presents the main CI Workflows for Supplier Portal.

# CircleCI Workflows

The main workflows are:
- `suricates_pull_request`: this workflow handles changes on Pull Requests branches
- `suricates-docs`: used for documentation branches

## `suricates_pull_request` Workflow

This workflow is triggered on commits on pull request branches. It ignores branches matching: `master`, `master-test` and `/doc-SP.*/`.

It is manually started by the user and has two manual approval steps:
* `ready_to_build?`: this launches the workflow.
* `Deploy Supplier Portal PR environment`: this launches the deployment workflow for Supplier Portal. Any demo deployment are automatically deleted after 24h.

The main steps are as :
- `ready_to_build?`
- `checkout`: Check out the branch to get the tag or commit.
- `build_srnt_dev`: build a docker image of PIM's dev version.
- `test_front_supplier_portal`: Launch tests for the front-end.
- `test_back_supplier_portal`: Launch tests for the back-end.
- `test_database`: Launch tests for the database.
- `test_helm_generated_k8s_files`: Validation of helm generated files.
- `Deploy Supplier Portal PR environment`: Ask confirmation to deploy the new version of the application.
- `build_srnt_prod`: Build a docker image of PIM's prod version.
- `deploy_pr_environment`:  Deploy PIM.
- `delete_pr_environment?`: Ask confirmation to delete the deployed environment for this PR.
- `delete_pr_environment`: Delete the deployed environment for this PR.
- `pull_request_success`: End of the successful jobs.

All production's deployment workflows and scheduled workflows are set in the PIM CI

## `suricates-docs` Workflow

This workflow is triggered by commits on the branches matching `/doc-SP.*/` - it avoids running the tests for branches containing only documentation updates.
It only runs the `workflow_success` job allowing to merge the PR.
To use it instead of `pull_request`, name your branch with the prefix `doc-SP`.

## Migration tests

Don't forget to tag Supplier Portal migration tests with `@group migration-supplier-portal`. This way, they will be launched from the `suricates_pull_request` workflow (inside the `test_back_supplier_portal` step).
If you don't put any tag, they will be only launched by the global `pull_request` and release workflows (it means it could break other squads builds after merging the PR).
