#!/usr/bin/env bash

# This script searches for PHP short echo tags that are not part of a $this-> call.
#
# Usage: ./script.sh [-p path] [-s suffix]
# -p: Path to search (optional, default: "src")
# -s: File suffix to search (optional, default: ".tpl.php")
#
# Example usage:
#   ./script.sh -p /some/path -s .php
#   ./script.sh -p /another/path
#   ./script.sh -s .tpl.php
#   ./script.sh

path="src"
suffix=".tpl.php"

# Parse options
while getopts "p:s:" opt; do
  case ${opt} in
    p )
      path=$OPTARG
      ;;
    s )
      suffix=$OPTARG
      ;;
    \? )
      echo "Invalid option: -$OPTARG" >&2
      exit 1
      ;;
    : )
      echo "Option -$OPTARG requires an argument." >&2
      exit 1
      ;;
  esac
done

# Execute the ripgrep command
if rg --no-heading --line-number --column --pcre2 '<\?=\s*(?!\s*\$this->)\s*.{1,7}' --glob "*$suffix" "$path"; then
    exit 1
else
    exit 0
fi
