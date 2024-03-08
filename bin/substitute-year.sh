#!/bin/bash

# Substituts year and version number
# Tags it searches for are %%YEAR%% and %%VERSION%%


log_step() {
  echo ">>> ${1}";
}

case "$OSTYPE" in
  solaris*) OS_VER="SOLARIS" ;;
  darwin*)  OS_VER="OSX" ;; 
  linux*)   OS_VER="LINUX" ;;
  bsd*)     OS_VER="BSD" ;;
  msys*)    OS_VER="WINDOWS" ;;
  cygwin*)  OS_VER="ALSO WINDOWS" ;;
  *)        OS_VER="unknown: $OSTYPE" ;;
esac

log_step "Replacing year"

year=$(date +%Y)

VERSION=$(grep 'Version:' footnotes-made-easy.php | sed 's/.*:[ \t]*//g')

log_step "Extracted version is $VERSION"

if [[ $OS_VER == *OSX* ]]; then
  find . -not -path '*/\.*' -not -path '*/vendor*' -not -path '*/node_modules*' -type f -name '*.php' -exec sed -i '' -e "s/%%YEAR%%/$year/g" {} \;
  find . -not -path '*/\.*' -not -path '*/vendor*' -not -path '*/node_modules*' -type f \( -name "*.php" -o -name "*.txt" \) -exec sed -i '' -e "s/ %%VERSION%%/ ${VERSION}/g" {} \;
else
  find . -not -path '*/\.*' -not -path '*/vendor*' -not -path '*/node_modules*' -type f -name '*.php' -exec sed -i  "s/'%%YEAR%%/$year/g" {} \;
  find . -not -path '*/\.*' -not -path '*/vendor*' -not -path '*/node_modules*' -type f \( -name "*.php" -o -name "*.txt" \) -exec sed -i  -e "s/ %%VERSION%%/ ${VERSION}/g" {} \;
fi

log_step "Substitution of the year is completed"