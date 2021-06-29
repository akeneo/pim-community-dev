#!/bin/bash

COUNT=$(find src -wholename '**/translations/*.??.yml'|wc -l)
if [ $COUNT -ne 0 ]; then
  echo "There are some legacy translations in this branch ($COUNT)"
  echo "Please remove these files before merge:"
  echo "rm $(find src -wholename '**/translations/*.??.yml'|tr '\n' ' ')"
  exit 1
fi

echo "No legacy translations were found."

exit 0
