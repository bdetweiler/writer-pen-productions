/***************************************************************************
                          menu_hardware_probe.cpp  -  description
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
/*
 * old
-------------------------------
| Wanpipe Hardware Probe Info |
-------------------------------
1 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=A : PORT=PRI
2 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=A : PORT=SEC
3 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=B : PORT=PRI
4 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=B : PORT=SEC
5 . S514-7-PCI : SLOT=3 : BUS=1 : IRQ=21 : CPU=A : PORT=PRI
6 . S514-7-PCI : SLOT=3 : BUS=1 : IRQ=21 : CPU=B : PORT=PRI
7 . AFT-A102   : SLOT=2 : BUS=1 : IRQ=22 : CPU=A : PORT=PRI
8 . AFT-A102   : SLOT=2 : BUS=1 : IRQ=22 : CPU=B : PORT=PRI
9 . AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : LINE=0
10. AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : LINE=1
11. AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : LINE=2
12. AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : LINE=3

Card Cnt: S508=0  S514X=2  S518=0  AFT=2
------------------------------------------------------------------
new:
-------------------------------
| Wanpipe Hardware Probe Info |
-------------------------------
1 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=A : PORT=PRI
2 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=A : PORT=SEC
3 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=B : PORT=PRI
4 . S514-2-PCI : SLOT=4 : BUS=0 : IRQ=18 : CPU=B : PORT=SEC
5 . S514-7-PCI : SLOT=3 : BUS=1 : IRQ=21 : CPU=A : PORT=PRI
6 . S514-7-PCI : SLOT=3 : BUS=1 : IRQ=21 : CPU=B : PORT=PRI
7 . AFT-A102   : SLOT=2 : BUS=1 : IRQ=22 : CPU=A : PORT=PRI
8 . AFT-A102   : SLOT=2 : BUS=1 : IRQ=22 : CPU=B : PORT=PRI
9 . AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : PORT=0
10. AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : PORT=1
11. AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : PORT=2
12. AFT-A104   : SLOT=6 : BUS=0 : IRQ=16 : CPU=A : PORT=3

*/

#include "menu_hardware_probe.h"
#include "text_box_help.h"

char* no_sangoma_cards_detected_str = "No Sangoma cards detected!";
char* hw_probe_help_str =
"Select a card for WANPIPE device\n"
"from a list of cards detected on the system.";

int get_slot_from_str(char * str_buff, unsigned* PCI_slot_no);
int get_bus_from_str(char * str_buff, unsigned* pci_bus_no);
int get_cpu_from_str(char * str_buff, char* S514_CPU_no);


//ISA
int get_io_port_from_str(char * str_buff, unsigned* ioport);

#define DBG_MENU_HARDWARE_PROBE 1

menu_hardware_probe::menu_hardware_probe( IN char * lxdialog_path,
                                          IN conf_file_reader* ptr_cfr)

{
  Debug(DBG_MENU_HARDWARE_PROBE, ("menu_hardware_probe::menu_hardware_probe()\n"));

  snprintf(this->lxdialog_path, MAX_PATH_LENGTH, "%s", lxdialog_path);
  this->cfr = ptr_cfr;
}

menu_hardware_probe::~menu_hardware_probe()
{
  Debug(DBG_MENU_HARDWARE_PROBE, ("menu_hardware_probe::~menu_hardware_probe()\n"));
}

int menu_hardware_probe::run(OUT int * selection_index)
{

  int rc;
  char tmp_buff[MAX_PATH_LENGTH];
  unsigned int option_selected;
  char exit_dialog;
  int number_of_items;

  //help text box
  text_box tb;

  link_def_t * link_def;
  wandev_conf_t *linkconf;
  sdla_fe_cfg_t*  fe_cfg;
 
  Debug(DBG_MENU_HARDWARE_PROBE, ("menu_net_interface_setup::run()\n"));

again:
  number_of_items = 6;
  rc = YES;
  option_selected = 0;
  exit_dialog = NO;

  link_def = cfr->link_defs;
  linkconf = cfr->link_defs->linkconf;
  fe_cfg   = &linkconf->fe_cfg;

  Debug(DBG_MENU_HARDWARE_PROBE, ("cfr->link_defs->name: %s\n", link_def->name));
  Debug(DBG_MENU_HARDWARE_PROBE, ("current card_type: DEC:%d, HEX: 0x%X\n",
    linkconf->card_type, linkconf->card_type));


  menu_str = " ";
  if(hardware_probe() == NO){
    return NO;
  }

  Debug(DBG_MENU_HARDWARE_PROBE, ("returned from: hardware_probe()\n"));

  //////////////////////////////////////////////////////////////////////////////////////
  //create the explanation text for the menu
  snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n------------------------------------------\
\nSelect card from the list of detected cards.");

  if(set_configuration(   YES,//indicates to call V2 of the function
                          MENU_BOX_BACK,//MENU_BOX_SELECT,
                          lxdialog_path,
                          "SELECT CARD FROM LIST",
                          WANCFG_PROGRAM_NAME,
                          tmp_buff,
                          MENU_HEIGTH, MENU_WIDTH+1,
                          number_of_items,
                          (char*)menu_str.c_str()
                          ) == NO){
    rc = NO;
    goto cleanup;
  }

  if(show(selection_index) == NO){
    rc = NO;
    goto cleanup;
  }
  //////////////////////////////////////////////////////////////////////////////////////

  exit_dialog = NO;
  switch(*selection_index)
  {
  case MENU_BOX_BUTTON_SELECT:
    Debug(DBG_MENU_HARDWARE_PROBE,
      ("hardware_setup: option selected for editing: %s\n", get_lxdialog_output_string()));

    //if no cards detected, return
    if(strcmp(replace_new_line_with_zero_term(get_lxdialog_output_string()),
              no_sangoma_cards_detected_str) == 0){
      rc = YES;
      goto cleanup;
    }

    if(parse_selected_card_line(
                  replace_new_line_with_zero_term(get_lxdialog_output_string()),
                  &linkconf->card_type,
                  &link_def->card_version) == NO){
      ERR_DBG_OUT(("Failed to get Card Type from selected card line!! line: %s\n",
        get_lxdialog_output_string()));
      rc = NO;
      goto cleanup;
    }

    //get card location
    if(get_card_location_from_hwprobe_line(linkconf,
			    		   link_def,
                      replace_new_line_with_zero_term(get_lxdialog_output_string())) == NO){
      ERR_DBG_OUT(("Failed to get Card Location from selected card line!! line: %s\n",
        get_lxdialog_output_string()));
      rc = NO;
      goto cleanup;
    }

    linkconf->auto_pci_cfg = WANOPT_NO;

    rc = YES;
    exit_dialog = YES;
    break;

  case MENU_BOX_BUTTON_HELP:
    tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, hw_probe_help_str);
    break;

  case MENU_BOX_BUTTON_EXIT:
    exit_dialog = YES;
    break;
  }//switch(*selection_index)

  if(exit_dialog == NO){
    goto again;
  }

cleanup:
  return rc;
}

int menu_hardware_probe::hardware_probe()
{
  int system_rc;
  FILE * hwprobe_file_tmp;
  char str_buff[MAX_PATH_LENGTH];
  char shell_command_line[MAX_PATH_LENGTH];
  char* tmp_hwprobe_file_full_path = "tmp_hwprobe_file";
  int probed_cards_count=0;
  string local_menu_str;

  local_menu_str = " ";

  snprintf(shell_command_line, MAX_PATH_LENGTH, "wanrouter hwprobe > %s", tmp_hwprobe_file_full_path);

  Debug(DBG_MENU_HARDWARE_PROBE, ("hardware_probe(): shell_command_line : %s\n",
    shell_command_line));
  system_rc = system(shell_command_line);
  if(system_rc){
    ERR_DBG_OUT(
      ("The hardware probe command failed! Check WANPIPE is installed properly.(system_rc : 0x%X)\n",
      system_rc));
    return NO;
  }


  if(verify_hwprobe_command_is_successfull() == NO){
    ERR_DBG_OUT(("Hardware probe failed!! Check wanpipe installation.\n"));
    return NO;
  }
	  
  hwprobe_file_tmp = fopen(tmp_hwprobe_file_full_path, "r+");
  if(hwprobe_file_tmp == NULL){
    ERR_DBG_OUT(("Failed to open %s file for reading!!\n", tmp_hwprobe_file_full_path));
    return NO;
  }

  do{
    fgets(str_buff, MAX_PATH_LENGTH, hwprobe_file_tmp);

    if(!feof(hwprobe_file_tmp)){
      Debug(DBG_MENU_HARDWARE_PROBE, ("%s\n", str_buff));

      //skip irrelevant lines
      //only lines starting with numbers (0 not allowed) should be displayed.
      if(atoi(&str_buff[0]) != 0){

        //this pointer skips the the line number by searching for first dot '.'
        char* tmp_str = strchr(str_buff, '.');
        if(tmp_str == NULL){
          ERR_DBG_OUT(("Invalid format of 'hwprobe' output line!! (not '.' found)\n"));
          return NO;
        }
        tmp_str += 2;

        //tokens separated by ':' chars and UNKNOWN number of ' ' (space) chars.
        //remove all ' ' chars, so only ':' will remain.
        tokenize_string(tmp_str, " ", shell_command_line, MAX_PATH_LENGTH);

        //now replace each ':' with a single ' ' (space), so it will be
        //displayed nicely in the dialog.
        replace_char_with_other_char_in_str(shell_command_line, ':', ' ');

        memcpy(str_buff, shell_command_line, MAX_PATH_LENGTH);

        replace_new_line_with_zero_term(str_buff);

        //tag and item strings are the same.
        snprintf(shell_command_line, MAX_PATH_LENGTH, " \"%s\" ", str_buff);
        Debug(DBG_MENU_HARDWARE_PROBE, ("1.shell_command_line: %s\n", shell_command_line));
        local_menu_str += shell_command_line;

        snprintf(shell_command_line, MAX_PATH_LENGTH, " \"%s\" ", str_buff);
        Debug(DBG_MENU_HARDWARE_PROBE, ("2.shell_command_line: %s\n", shell_command_line));
        local_menu_str += shell_command_line;

        probed_cards_count++;
      }
    }

  }while(!feof(hwprobe_file_tmp));

  fclose(hwprobe_file_tmp);

  snprintf(shell_command_line, MAX_PATH_LENGTH, "rm -rf %s", tmp_hwprobe_file_full_path);
  system_rc = system(shell_command_line);
  if(system_rc){
    ERR_DBG_OUT(("Failed to remove temporary file: %s! (system_rc : 0x%X)\n",
      tmp_hwprobe_file_full_path, system_rc));
  }

  if(probed_cards_count == 0){
    local_menu_str = " ";
    snprintf(shell_command_line, MAX_PATH_LENGTH, " \"%s\" ", no_sangoma_cards_detected_str);
    local_menu_str += shell_command_line;
    snprintf(shell_command_line, MAX_PATH_LENGTH, " \"%s\" ", no_sangoma_cards_detected_str);
    local_menu_str += shell_command_line;
  }

  menu_str = local_menu_str.c_str();

  Debug(DBG_MENU_HARDWARE_PROBE, ("returning from hardware_probe()\n"));
  return YES;
}

int menu_hardware_probe::parse_selected_card_line(char* selected_card_line,
                                                  char* card_type,
                                                  unsigned int* card_version)
{
  char * tmp;
  int rc = NO;

  //
  //S514 cards:
  //
  tmp = strstr(selected_card_line, "S514-1");
  if(tmp != NULL){
    rc = YES;
    *card_type = WANOPT_S51X;
    *card_version = S5141_ADPTR_1_CPU_SERIAL;
    goto done;
  }

  tmp = strstr(selected_card_line, "S514-2");
  if(tmp != NULL){
    rc = YES;
    //used the same way as S5141
    *card_type = WANOPT_S51X;
    *card_version = S5141_ADPTR_1_CPU_SERIAL;
    goto done;
  }

  tmp = strstr(selected_card_line, "S514-3");
  if(tmp != NULL){
    rc = YES;
    //FT1 is the same as Serial S5141
    *card_type = WANOPT_S51X;
    *card_version = S5141_ADPTR_1_CPU_SERIAL;
    goto done;
  }

  tmp = strstr(selected_card_line, "S514-4");
  if(tmp != NULL){
    rc = YES;
    *card_type = WANOPT_S51X;
    *card_version = S5144_ADPTR_1_CPU_T1E1;
    goto done;
  }

  tmp = strstr(selected_card_line, "S514-5");
  if(tmp != NULL){
    rc = YES;
    *card_type = WANOPT_S51X;
    *card_version = S5145_ADPTR_1_CPU_56K;
    goto done;
  }

  tmp = strstr(selected_card_line, "S514-7");
  if(tmp != NULL){
    rc = YES;
    //the same as S5144
    *card_type = WANOPT_S51X;
    *card_version = S5144_ADPTR_1_CPU_T1E1;
    goto done;
  }

  tmp = strstr(selected_card_line, "S514-8");
  if(tmp != NULL){
    rc = YES;
    //the same as S5144
    *card_type = WANOPT_S51X;
    *card_version = S5144_ADPTR_1_CPU_T1E1;
    goto done;
  }

  //
  //POS card treated as S5141
  //
  tmp = strstr(selected_card_line, "S515");
  if(tmp != NULL){
    rc = YES;
    //the same as S5141
    *card_type = WANOPT_S51X;
    *card_version = S5144_ADPTR_1_CPU_T1E1;
    goto done;
  }

  //
  //S508 card:
  //
  tmp = strstr(selected_card_line, "S508");
  if(tmp != NULL){
    rc = YES;
    *card_type = WANOPT_S50X;
    *card_version = NOT_SET;
    goto done;
  }

  //
  //ADSL card
  //
  tmp = strstr(selected_card_line, "S518");
  if(tmp != NULL){
    rc = YES;
    *card_type = WANOPT_ADSL;
    *card_version = NOT_SET;
    goto done;
  }

  //
  //A101/2 (AFT) T1/E1 card
  //
  if( strstr(selected_card_line, "A100") != NULL ||
      strstr(selected_card_line, "AFT-A101") != NULL ||
      strstr(selected_card_line, "AFT-A102") != NULL ){
    rc = YES;
    *card_type = WANOPT_AFT;
    *card_version = A101_ADPTR_1TE1;//WAN_MEDIA_T1;//indicates A101
    goto done;
  }

  //
  //A104 (AFT) quad port T1/E1 card
  //
  if( strstr(selected_card_line, "AFT-A104") != NULL){

    Debug(DBG_MENU_HARDWARE_PROBE, ("found \"AFT-A104\" string\n"));
    
    rc = YES;
    *card_type = WANOPT_AFT;
    *card_version = A104_ADPTR_4TE1;//indicates A104
    goto done;
  }
  
  //
  //A105 (AFT) T3/E3 card
  //
  if( strstr(selected_card_line, "A105-1-PCI") != NULL ||
      strstr(selected_card_line, "A105-2-PCI") != NULL ||
      strstr(selected_card_line, "AFT-A300") != NULL){
    rc = YES;
    *card_type = WANOPT_AFT;
    *card_version = A300_ADPTR_U_1TE3;//WAN_MEDIA_DS3;//indicates A105
    goto done;
  }

done:

  return rc;
}

int menu_hardware_probe::get_card_location_from_hwprobe_line( wandev_conf_t* linkconf,
							      link_def_t * link_def,
                                                              char* selected_card_line)
{
  text_box tb;
  sdla_fe_cfg_t*  fe_cfg;

  fe_cfg   = &linkconf->fe_cfg;
  
  switch(linkconf->card_type)
  {
  case WANOPT_S50X:
    //get ISA stuff. only IO port is detected
    if(get_io_port_from_str(selected_card_line, &linkconf->ioport) == NO){
      ERR_DBG_OUT(("Failed to get 'ioport' from line: %s!\n",
        selected_card_line));
      return NO;
    }
    break;

  case WANOPT_S51X:
  case WANOPT_ADSL:
  case WANOPT_AFT:
    //get PCI stuff
    if(get_slot_from_str(selected_card_line, &linkconf->PCI_slot_no) == NO){
      ERR_DBG_OUT(("Failed to get 'PCI_slot_no' from line: %s!\n",
        selected_card_line));
      return NO;
    }

    if(get_bus_from_str(selected_card_line, &linkconf->pci_bus_no) == NO){
      ERR_DBG_OUT(("Failed to get 'pci_bus_no' from line: %s!\n",
        selected_card_line));
      return NO;
    }

    //ADSL card always have only one CPU. But check anyway
    if(get_cpu_from_str(selected_card_line, linkconf->S514_CPU_no) == NO){
      ERR_DBG_OUT(("Failed to get 'S514_CPU_no' from line: %s!\n",
        selected_card_line));
      return NO;
    }
    break;

  default:
    ERR_DBG_OUT(("Unsupported card type selected %d!\n",
      linkconf->card_type));

    tb.show_error_message(lxdialog_path, NO_PROTOCOL_NEEDED,
      "Unsupported card type selected");
    return NO;
  }

  if(link_def->card_version != A104_ADPTR_4TE1){
  	//get the port
  	return get_port_from_str(selected_card_line, &linkconf->comm_port);
  }else{
	//get line_no
	return get_line_number_from_str(selected_card_line, &fe_cfg->line_no);
  }
}

int get_slot_from_str(char * str_buff, unsigned* PCI_slot_no)
{
  char * tmp;
  int i_tmp;

  tmp = strstr(str_buff, "SLOT=");
  if(tmp == NULL){
    return NO;
  }

  tmp += strlen("SLOT=");
  i_tmp = atoi(tmp);

  Debug(DBG_MENU_HARDWARE_PROBE, ("get_slot_from_str() : slot : %d\n", i_tmp));

  *PCI_slot_no = i_tmp;
  return YES;
}

int get_bus_from_str(char * str_buff, unsigned* pci_bus_no)
{
  char * tmp;
  int i_tmp;

  tmp = strstr(str_buff, "BUS=");
  if(tmp == NULL){
    return NO;
  }

  tmp += strlen("BUS=");
  i_tmp = atoi(tmp);

  Debug(DBG_MENU_HARDWARE_PROBE, ("get_bus_from_str() : bus : %d\n", i_tmp));

  *pci_bus_no = i_tmp;
  return YES;
}

int get_cpu_from_str(char * str_buff, char* S514_CPU_no)
{
  char * tmp;
  int i_tmp;

  tmp = strstr(str_buff, "CPU=");
  if(tmp == NULL){
    return NO;
  }

  tmp += strlen("CPU=");
  i_tmp = *tmp;

  Debug(DBG_MENU_HARDWARE_PROBE,
    ("get_cpu_from_str() : CPU : %c\n", (i_tmp == 'A') ? 'A' : 'B'));

  //*S514_CPU_no = i_tmp;
  S514_CPU_no[0] = i_tmp;
  return YES;
}

int menu_hardware_probe::get_port_from_str(char * str_buff, int* comm_port)
{
  char * tmp = str_buff;
  
  link_def_t * link_def;
  wandev_conf_t *linkconf;

  link_def = cfr->link_defs;
  linkconf = cfr->link_defs->linkconf;

  Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): str_buff: %s\n", str_buff));

  //for ISA card the 'ioport' is displayed first,
  //it may fail the 'comms port' parsing. skip past the 'ioport'.
  if(linkconf->card_type == WANOPT_S50X){
    tmp = strstr(str_buff, "IOPORT=");
    if(tmp == NULL){
      Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): failed to find IOPORT str.\n"));
      return NO;
    }
    Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): skipping past IOPORT...\n"));
    tmp += strlen("IOPORT=");
  }

  tmp = strstr(tmp, "PORT=");
  if(tmp == NULL){
    return NO;
  }

  tmp += strlen("PORT=");

  if(strcmp(tmp, "PRI") == 0){
    *comm_port = 0;
    Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): PORT : PRI\n"));
  }else if(strcmp(tmp, "SEC") == 0){
    *comm_port = 1;
    Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): PORT : SEC\n"));
  }else{
    Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): Invalid Port!!\n"));
    ERR_DBG_OUT(("Failed to get 'comm_port' from line: %s!\n", str_buff));
    return NO;
  }

  return YES;
}

int get_io_port_from_str(char * str_buff, unsigned* ioport)
{
  char * tmp;
  
  tmp = strstr(str_buff, "IOPORT");
  if(tmp == NULL){
    return NO;
  }

  //skip past '='
  tmp = strstr(str_buff, "=");
  if(tmp == NULL){
    return NO;
  }
  tmp += strlen("=");

  *ioport = strtoul(tmp, NULL, 16);
  Debug(DBG_MENU_HARDWARE_PROBE, ("get_port_from_str(): IOPORT: 0x%X\n", *ioport));

  return YES;
}

//original 'LINE=' was changed to 'PORT=' in the 'hwprobe'
int menu_hardware_probe::get_line_number_from_str(char * str_buff, unsigned int* line_no)
{
  char * tmp = str_buff;

  Debug(DBG_MENU_HARDWARE_PROBE, ("get_line_number_from_str(): str_buff: %s\n", str_buff));

  //tmp = strstr(tmp, "LINE=");
  tmp = strstr(tmp, "PORT=");
  if(tmp == NULL){
    return NO;
  }

  //tmp += strlen("LINE=");
  tmp += strlen("PORT=");

  *line_no = atoi(tmp);

  Debug(DBG_MENU_HARDWARE_PROBE, ("got line_no: %d\n", *line_no));

  return YES;
}

int menu_hardware_probe::verify_hwprobe_command_is_successfull()
{ 
  char* expected_str = "Wanpipe Hardware Probe Info";
  FILE * hwprobe_file_tmp;
  char str_buff[MAX_PATH_LENGTH];
  char* tmp_hwprobe_file_full_path = "tmp_hwprobe_file";
  char rc = NO;

  hwprobe_file_tmp = fopen(tmp_hwprobe_file_full_path, "r+");
  if(hwprobe_file_tmp == NULL){
    ERR_DBG_OUT(("Failed to open %s file for reading!!\n", tmp_hwprobe_file_full_path));
    return NO;
  }

  do{
    fgets(str_buff, MAX_PATH_LENGTH, hwprobe_file_tmp);

    if(!feof(hwprobe_file_tmp)){
      Debug(DBG_MENU_HARDWARE_PROBE, ("%s\n", str_buff));
      //search for the 'expected_str'
      
      if(strstr(str_buff, expected_str)){
	//exit(0);      
	rc = YES;
	break;
      }
    }

  }while(!feof(hwprobe_file_tmp));

  fclose(hwprobe_file_tmp);
  
  return rc;
}

