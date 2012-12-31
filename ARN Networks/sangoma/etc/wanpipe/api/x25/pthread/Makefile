CC=g++
SYSINC=/usr/src/linux/include
APIINC=../../include
CFLAGS=-O -I.. -D_REENTRANT -lpthread
CFLAGS1=-w -c -D_REENTRANT -D_GNUC_  -D__LINUX__ -I$(SYSINC) -I$(APIINC)

server: bitmap.o server.o
	$(CC) $(CFLAGS) bitmap.o server.o -o server 
	
server.o: server.h server.c	
	$(CC) $(CFLAGS1) server.c

bitmap.o: bitmap.h bitmap.c
	$(CC) $(CFLAGS1) bitmap.c

clean:	
	rm -f server
	rm *.o
