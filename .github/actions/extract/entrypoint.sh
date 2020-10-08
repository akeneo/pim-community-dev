#!/bin/sh -l

echo "Starts"
FOLDER="$1"
GITHUB_USERNAME="$2"
GITHUB_REPO="$3"
USER_EMAIL="$4"
BRANCH="$5"
COMMIT_MESSAGES_FILEPATH="$6"

CLONE_DIR=$(mktemp -d)

# Setup git
git config --global user.email "$USER_EMAIL"
git config --global user.name "$GITHUB_USERNAME"
git clone "https://$API_TOKEN_GITHUB@github.com/$GITHUB_USERNAME/$GITHUB_REPO.git" "$CLONE_DIR" --branch "$BRANCH"

# Copy files into the git and deletes all git
find "$CLONE_DIR" | grep -v "^$CLONE_DIR/\.git" | grep -v "^$CLONE_DIR$" | xargs rm -rf # delete all files (to handle deletions)

rm -rf "$CLONE_DIR"/.github
rm "$CLONE_DIR"/.gitignore

cp -r "$FOLDER"/. "$CLONE_DIR"

cd "$CLONE_DIR"
git status

git add .
git commit --file "$COMMIT_MESSAGES_FILEPATH"
git push origin "$BRANCH"
