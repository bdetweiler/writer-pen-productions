# ============================================================================
# Makefile	Make script for building Linux WAN router utilities.
# ----------------------------------------------------------------------------
# Copyright (c) 1995-1999 Sangoma Technologies Inc.  All Rights Reserved.
# ============================================================================

####### MACROS ###############################################################

# Build options.
OS_TYPE	= _LINUX_
DEBUG	= 2

# Project file paths.
SYSINC = /usr/src/linux/include

HOSTCFLAGS=-I/usr/src/linux/include 
ifeq (/usr/include/ncurses/ncurses.h, $(wildcard /usr/include/ncurses/ncurses.h))
        HOSTCFLAGS += -I/usr/include/ncurses
else
ifeq (/usr/include/ncurses/curses.h, $(wildcard /usr/include/ncurses/curses.h))
        HOSTCFLAGS += -I/usr/include/ncurses
else
endif
endif

# Tools options.
CFLAGS  = -D__LINUX__ -Wall -O2 -D_GNUC_ -I$(SYSINC) -D$(OS_TYPE) -D_DEBUG_=$(DEBUG) $(HOSTCFLAGS) 
LIBS = -lncurses

####### RULES ################################################################

all:	wanpipe_ft1exec
	@echo "Ok."

wanpipe_ft1exec:	ft1_config.c	unixio.c
	$(CC) $(CFLAGS) -o $@ $^
	chmod 755 $@

clean:
	rm -f wanpipe_ft1exec
	rm -f *.o

install:
	install -D -m 755 wanpipe_ft1exec $(WAN_VIRTUAL)/usr/sbin/wanpipe_ft1exec
	install -D -m 755 wanpipe_cfgft1 $(WAN_VIRTUAL)/usr/sbin/cfgft1

uninstall:
	rm -f $(WAN_VIRTUAL)/usr/sbin/wanpipe_ft1exec
	rm -f $(WAN_VIRTUAL)/usr/sbin/cfgft1
