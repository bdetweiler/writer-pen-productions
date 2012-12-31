# ============================================================================
# Makefile	Make script for building Linux WAN router utilities.
# ----------------------------------------------------------------------------
# Copyright (c) 1995-2000 Sangoma Technologies Inc.  All Rights Reserved.
# ============================================================================

####### MACROS ###############################################################

# Build options.
OS_TYPE	= __LINUX__
DEBUG	= 2

PROD=wanpipemon_legacy
# Project file paths.
SYSINC	= /usr/src/linux/include
WAN_VIRTUAL=
LXDIR=../lxdialog
ENABLE_GUI=YES
PROTOCOL_DEFS=
#-DCONFIG_PRODUCT_WANPIPE_ADSL

HOSTCFLAGS=-I$(SYSINC)
ifeq (/usr/include/ncurses/ncurses.h, $(wildcard /usr/include/ncurses/ncurses.h))
        HOSTCFLAGS += -I/usr/include/ncurses
else
ifeq (/usr/include/ncurses/curses.h, $(wildcard /usr/include/ncurses/curses.h))
        HOSTCFLAGS += -I/usr/include/ncurses
else
endif
endif

# Tools options.
CFLAGS  = -Wall -O2 -D$(OS_TYPE) -I$(SYSINC) -I../ft1 -D_DEBUG_=$(DEBUG) $(HOSTCFLAGS)

ifeq "${ENABLE_GUI}" "NO"
LX_OBJ =
else
LX_OBJ = wangui.c $(LXDIR)/checklist.o $(LXDIR)/menubox.o $(LXDIR)/inputbox.o $(LXDIR)/util.o -lncurses
CFLAGS += -DWANPIPEMON_GUI -DLOCALE  

ifeq (/usr/include/ncurses/ncurses.h, $(wildcard /usr/include/ncurses/ncurses.h))
        CFLAGS += -I/usr/include/ncurses -DCURSES_LOC="<ncurses.h>"
else
ifeq (/usr/include/ncurses/curses.h, $(wildcard /usr/include/ncurses/curses.h))
        CFLAGS += -I/usr/include/ncurses -DCURSES_LOC="<ncurses/curses.h>"
else
ifeq (/usr/include/ncurses.h, $(wildcard /usr/include/ncurses.h))
        CFLAGS += -DCURSES_LOC="<ncurses.h>"
else
	CFLAGS += -DCURSES_LOC="<curses.h>"
endif
endif
endif

endif

LIB_SRC = fe_lib.c xml_lib.c ../ft1/unixio.c
STANDARD_SRC = fpipemon.c cpipemon.c ppipemon.c xpipemon.c atmpipemon.c aftpipemon.c dslpipemon.c prot_trace.c ss7pipemon.c bpipemon.c

####### RULES ################################################################

all: $(PROD) 
	@echo "Ok."

wanpipemon_legacy:   wanpipemon.c $(STANDARD_SRC) $(LIB_SRC) $(LX_OBJ)
	$(CC) -D_GNUC_ $(CFLAGS) -o $@ $^

install:
	@echo "Installing Wanpipe utilites in $(WAN_VIRTUAL)/usr/sbin"
	install -D wanpipemon_legacy	  $(WAN_VIRTUAL)/usr/sbin/wanpipemon_legacy

uninstall:
	@echo "Un-installing Wanpipe utilites from $(WAN_VIRTUAL)/usr/sbin"
	rm -f $(WAN_VIRTUAL)/usr/sbin/wanpipemon_legacy
clean:
	rm -f *.o
	rm -f wanpipemon_legacy
