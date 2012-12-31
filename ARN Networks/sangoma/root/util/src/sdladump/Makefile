# ============================================================================
# Makefile	Make script for building Linux WAN router utilities.
# ----------------------------------------------------------------------------
# Copyright (c) 1995-2000 Sangoma Technologies Inc.  All Rights Reserved.
# ============================================================================

####### MACROS ###############################################################

# Build options.
DEBUG	= 2

# Project file paths.
SYSINC	= /usr/src/linux/include

# Tools options.
CFLAGS  = -D__LINUX__ -I$(SYSINC) -Wall -O2  -D_DEBUG_=$(DEBUG) 


####### RULES ################################################################

all: sdladump
	@echo "Ok."

sdladump:	sdladump.c 
	$(CC) $(CFLAGS) -o $@ $<


install:
	@echo "Installing Sdladump in $(WAN_VIRTUAL)/usr/sbin"
	install -D sdladump	  $(WAN_VIRTUAL)/usr/sbin/sdladump

uninstall:
	@echo "Un-installing Sdladump from $(WAN_VIRTUAL)/usr/sbin"
	rm -f $(WAN_VIRTUAL)/usr/sbin/sdladump

clean:
	rm -f *.o
	rm -f sdladump
