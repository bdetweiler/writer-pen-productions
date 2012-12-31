/***************************************************************************
                          menu_hardware_probe.h  -  description
                             -------------------
    begin                : Wed Mar 31 2004
    copyright            : (C) 2004 by David Rokhvarg
    email                : davidr@sangoma.com
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

#ifndef MENU_HARDWARE_PROBE_H
#define MENU_HARDWARE_PROBE_H

#include <menu_base.h>
#include "conf_file_reader.h"

/**
  *@author David Rokhvarg
  */

class menu_hardware_probe : public menu_base  {

  string menu_str;
  char lxdialog_path[MAX_PATH_LENGTH];
  conf_file_reader* cfr;

  int hardware_probe();

  int parse_selected_card_line(char* selected_card_line,
                               char* card_type,
                               unsigned int* card_version);

  int get_card_location_from_hwprobe_line(wandev_conf_t* linkconf,
		  			  link_def_t * link_def,
                                          char* selected_card_line);

  int get_port_from_str(char * str_buff, int* comm_port);
  
  int get_line_number_from_str(char * str_buff, unsigned int* line_no);

  int verify_hwprobe_command_is_successfull();

public:
	menu_hardware_probe(  	IN char * lxdialog_path,
                      		IN conf_file_reader* ptr_cfr);

	~menu_hardware_probe();

	int run(OUT int * selection_index);
};

#endif
