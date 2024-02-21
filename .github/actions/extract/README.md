# github-action-push-to-another-repository

Used to push generated files from a directory from Github Action step into another repository on Github.

## Inputs

### `source-directory` (argument)

From the repository that this Github Action is executed the directory that contains the files to be pushed into the repository.

### `destination-github-username` (argument)

For the repository `https://github.com/akeneo/akeneo-design-system` is `akeneo`. It's also used for the `Author:` in the generated git messages.

### `destination-repository-name` (argument)

For the repository `https://github.com/akeneo/akeneo-design-system` is `akeneo-design-system`

### `destination-branch` (argument)

The branch that will be pushed on the destination-repository-name.

### `commit-messages-filepath` (argument)

The filepath to the file containing commit messages

### `user-email` (argument)

The email that will be used for the commit in the destination-repository-name.

### `API_TOKEN_GITHUB` (environment)

E.g.:
`API_TOKEN_GITHUB: ${{ secrets.API_TOKEN_GITHUB }}`

Generate your personal token following the steps:

- Go to the Github Settings (on the right hand side on the profile picture)
- On the left hand side pane click on "Developer Settings"
- Click on "Personal Access Tokens" (also available at https://github.com/settings/tokens)
- Generate a new token, choose "Repo". Copy the token.

Then make the token available to the Github Action following the steps:

- Go to the Github page for the repository that you push from, click on "Settings"
- On the left hand side pane click on "Secrets"
- Click on "Add a new secret" and name it "API_TOKEN_GITHUB"

## Example usage

```yaml
- name: Pushes to another repository
  uses: ./.github/actions/extract
  env:
    API_TOKEN_GITHUB: ${{ secrets.API_TOKEN_GITHUB }}
  with:
    source-directory: akeneo-design-system
    destination-github-username: 'akeneo'
    destination-repository-name: 'akeneo-design-system'
    user-email: contact@akeneo.com
    destination-branch: 'master'
    commit-messages-filepath: '/github/workspace/commit-messages.txt'
```
