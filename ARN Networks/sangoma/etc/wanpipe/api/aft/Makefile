# ============================================================================
# Makefile	Make script for building Linux WAN router utilities.
# ----------------------------------------------------------------------------
# Copyright (c) 1995-1998 Sangoma Technologies Inc.  All Rights Reserved.
# ============================================================================

####### MACROS ###############################################################

# Build options.
OS_TYPE	= __LINUX__
DEBUG	= 2

# Project file paths.
SYSINC	= /usr/src/linux/include
VPATH	= $(SYSINC)

# Tools options.
CFLAGS	= -Wall -O2 -D$(OS_TYPE) -D_DEBUG_=$(DEBUG) -D_GNUC_ -I../lib -I$(SYSINC)

####### RULES ################################################################

all:	aft_api aft_api_rbs 
	@echo "Ok."

aft_api:     	aft_api.c ../lib/lib_api.c
	$(CC) $(CFLAGS) -o $@ $^
	
aft_api_rbs:     	aft_api_rbs.c ../lib/lib_api.c
	$(CC) $(CFLAGS) -o $@ $^

clean:
	rm -f aft_api
	rm -f aft_api_rbs
