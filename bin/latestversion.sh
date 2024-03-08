#!/bin/bash

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

log_step "Replacing latest version"

log_step "Extract the version from the plugin"

VERSION=$(grep 'Version:' footnotes-made-easy.php | sed 's/.*:[ \t]*//g')

log_step "Extracted version is $VERSION"

if [[ $OS_VER == *OSX* ]]; then
  find . -not -path '*/\.*' -not -path '*/vendor*' -not -path '*/node_modules*' -type f -name '*.php' -exec sed -i '' -e "s/\* @since\( \{1,\}\)latest/\* @since\1${VERSION}/g" {} \;
else
  find . -not -path '*/\.*' -not -path '*/vendor*' -not -path '*/node_modules*' -type f -name '*.php' -exec sed -i  "s/\* @since\( \{1,\}\)latest/\* @since\1${VERSION}/g" {} \;
fi

log_step "Substitution of the version is completed"