# ============================================================================
# Makefile	Make script for building Linux WAN router utilities.
# ----------------------------------------------------------------------------
# Copyright (c) 1995-2000 Sangoma Technologies Inc.  All Rights Reserved.
# ============================================================================

####### MACROS ###############################################################

# Build options.
OS_TYPE	= __LINUX__
DEBUG	= 2

# Project file paths.
SYSINC=/usr/src/linux/include
WAN_VIRTUAL=

# Tools options.
CFLAGS  = -Wall -O2 -D$(OS_TYPE) -I$(SYSINC) -D_DEBUG_=$(DEBUG)

####### RULES ################################################################

all: wanconfig
	@echo "Ok."

wanconfig:	wanconfig.c 
	$(CC) $(CFLAGS) -o $@ $<

install:
	@echo "Installing Wanpipe utilites in $(WAN_VIRTUAL)/usr/sbin"
	install -D wanconfig  $(WAN_VIRTUAL)/usr/sbin/wanconfig

uninstall:
	@echo "Un-installing Wanpipe utilites from $(WAN_VIRTUAL)/usr/sbin"
	rm -f $(WAN_VIRTUAL)/usr/sbin/wanconfig

clean:
	rm -f wanconfig 
