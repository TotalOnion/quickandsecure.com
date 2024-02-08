#!/usr/bin/env bash
set -eo pipefail

#
# Share a Lando based WordPress website using ngrok
# Cal Evans <cal@calevans.com>
#
# This script must be run OUTSIDE of the Lando environment but in the Lando dir.
# It uses the lando info command which is not available inside of the lando container.
#
# This is designed to work with a WordPress recipie. To make WordPress play nice
# you have to install the plugin at https://github.com/jonathanbardo/WP-Ngrok-Local
# Once you have that installed and activated, run this script and then visit
# the https URL that it shows.
#
# When you are done, press CTRL+C to exit.
#

#
# Check for sane environment
#
if ! command -v ngrok &> /dev/null
then
  echo ngrok is not installed.
  echo Visit https://ngrok.com/download and download the version for your OS.
  exit 1
fi

if ! command -v jq &> /dev/null
then
  echo jq is not installed.
  echo Visit https://stedolan.github.io/jq/download/ and download the version for your OS.
  echo If you have one, you can usually use your package manager to install it.
  exit 2
fi

# Get the https url from lando
#
FULL_SITE_NAME=`lando info --format json | jq '.[0].urls[-1]' | tr '"' ' '`

#
# Strip off the protocol
#
SITE_NAME=`echo $FULL_SITE_NAME | awk -F '//' {'print $2'}`
SITE_NAME=${SITE_NAME::-1}

#
# Call ngrok
#
ngrok http --host-header=$SITE_NAME --domain=stirred-gecko-nearly.ngrok-free.app $FULL_SITE_NAME