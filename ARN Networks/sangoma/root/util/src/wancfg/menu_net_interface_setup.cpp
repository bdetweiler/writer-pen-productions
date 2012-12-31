/***************************************************************************
                          menu_net_interface_setup.cpp  -  description
                             -------------------
    begin                : Tue Mar 23 2004
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

#include "menu_net_interface_setup.h"
#include "text_box_help.h"
#include "input_box.h"
#include "text_box_yes_no.h"

#include "net_interface_file_reader.h"
#include "menu_net_interface_ip_configuration.h"
#include "menu_net_interface_operation_mode.h"
#include "menu_net_interface_miscellaneous_options.h"

enum NET_IF_OPTIONS{
  IF_NAME,
  OPERATION_MODE,
  IP_SETUP,
  MISC_OPTIONS,
  TTY_MINOR_NUMBER,
  EMPTY_LINE,
  TDMV_SPAN_NUMBER,
  TDMV_ECHO_OPTIONS
};

char* net_if_name_help_str =
"Interface Name\n"
"--------------\n"
"\n"
"Name of the wanpipe interface which will\n"
"be displayed in ifconfig. This interface name will\n"
"also be associated with the local and remote IP of\n"
"device wanpipe2.\n"
"\n"
"The Wanpipe configurator always prepends the\n"
"selected interface name with 'wp#' where #=1,2,3... is\n"
"the device number selected previously. In this case the\n"
"interface name will be prepended with 'wp2'.\n"
"eg:\n"
"The way to decode the interface name: 'wp1ppp'\n"
"is the following: Interface name ppp on device\n"
"wanpipe1.\n";
//"Minimum name length is 1, maximum is 15 characters.\n";

char* operation_mode_help_str =
"Operation Mode\n"
"==============\n"
"\n"
"Wanpipe drivers can work in four modes:\n"
"\n"
"WANPIPE Mode\n"
"------------\n"
"Driver links to the IP stack, and behaves like\n"
"an Ethernet device. The linux routing stack\n"
"determines how to send and receive packets.\n"
"\n"
"API Mode:\n"
"---------\n"
"Wanpipe driver uses a special RAW socket to\n"
"directly couple the user application to the driver.\n"
"Thus, user application can send and receive raw data\n"
"to and from the wanpipe driver.\n"
"\n"
"BRIDGE Mode:\n"
"------------\n"
"For each frame relay network interface\n"
"a virtual ethernet network interface is configured.\n"
"\n"
"Since the virtual network interface is on a BRIDGE\n"
"device it is linked into the kernel bridging code.\n"
"\n"
"Once linked to the kenrel bridge, ethernet\n"
"frames can be transmitted over a WANPIPE frame\n"
"relay connections.\n"
"\n"
"BRIDGED NODE Mode:\n"
"------------------\n"
"For each frame relay network interface\n"
"a virtual ethernet netowrk interface is configured.\n"
"\n"
"However, this interface is not on the bridge itself,\n"
"it is connected remotely via frame relay link to the\n"
"bridge, thus it is  a node.\n"
"\n"
"Since the remote end of the frame link is connected\n"
"to the bridge, this interface will be able to send\n"
"and receive ethernet frames over the frame relay\n"
"line.\n"
"Note: Bridging is only supprted under Frame Relay\n";

char* ip_help_str =
"IP Address Setup\n"
"----------------\n"
"\n"
"IP addresses must be defined for each\n"
"network interface.\n";

char* net_if_miscellaneous_help_str =
"ADVANCED OPTIONS:\n"
"---------------------\n"
"1. Network Interface Start script\n"
"2. Network Interface Stop script\n"
"3. Multicast option\n"
"4. IPX support\n"
"5. IPX address\n"
"6. True Encoding option\n";



#define DBG_MENU_NET_INTERFACE_SETUP 1

menu_net_interface_setup::menu_net_interface_setup( IN char * lxdialog_path,
                                                    IN list_element_chan_def* list_element_logical_ch)
{
  Debug(DBG_MENU_NET_INTERFACE_SETUP,
    ("menu_net_interface_setup::menu_net_interface_setup()\n"));

  snprintf(this->lxdialog_path, MAX_PATH_LENGTH, "%s", lxdialog_path);

  this->list_element_logical_ch = list_element_logical_ch;
}

menu_net_interface_setup::~menu_net_interface_setup()
{
  Debug(DBG_MENU_NET_INTERFACE_SETUP,
    ("menu_net_interface_setup::~menu_net_interface_setup()\n"));
}

int menu_net_interface_setup::run(OUT int * selection_index)
{
  string menu_str;
  int rc;
  char tmp_buff[MAX_PATH_LENGTH];
  unsigned int option_selected;
  char exit_dialog;
  int name_len;
  int protocol;
  net_interface_file_reader* interface_file_reader = NULL;
  chan_def_t* chandef;

  //help text box
  text_box tb;

  input_box inb;
  char backtitle[MAX_PATH_LENGTH];
  char explanation_text[MAX_PATH_LENGTH];
  char initial_text[MAX_PATH_LENGTH];

  snprintf(backtitle, MAX_PATH_LENGTH, "WANPIPE Configuration Utility: ");
  snprintf(&backtitle[strlen(backtitle)], MAX_PATH_LENGTH,
          "%s", get_protocol_string(NO_PROTOCOL_NEEDED));

  Debug(DBG_MENU_NET_INTERFACE_SETUP, ("menu_net_interface_setup::run()\n"));

again:

  rc = YES;
  option_selected = 0;
  exit_dialog = NO;

  chandef = &list_element_logical_ch->data;
 
  if(interface_file_reader != NULL){
    delete interface_file_reader;
    interface_file_reader = NULL;
  }

  interface_file_reader = new net_interface_file_reader(chandef->name);

  if(interface_file_reader->parse_net_interface_file() == NO){
    Debug(DBG_MENU_NET_INTERFACE_SETUP, ("parse_net_interface_file() FAILED!!\n"));
    return NO;
  }


  Debug(DBG_MENU_NET_INTERFACE_SETUP, ("chandef->name: %s\n", chandef->name));
  
  /////////////////////////////////////////////////
  //on user input: check ifname is not longer than WAN_IFNAME_SZ
  menu_str.sprintf(" \"%d\" ", IF_NAME);
  snprintf(tmp_buff, MAX_PATH_LENGTH, " \"Interface Name-----> %s\" ", chandef->name);
  menu_str += tmp_buff;

  /////////////////////////////////////////////////
  if(chandef->usedby != TTY){

    //the 'usedby' selection available only for non-STACK interfaces.
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", OPERATION_MODE);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"Operation Mode-----> %s\" ",
      get_used_by_string(chandef->usedby));
    menu_str += tmp_buff;
  }

  /////////////////////////////////////////////////
//  Debug(DBG_MENU_NET_INTERFACE_SETUP, ("strlen(interface_file_reader.if_config.ipaddr): %d\n",
//    strlen(interface_file_reader.if_config.ipaddr)));

  Debug(DBG_MENU_NET_INTERFACE_SETUP, ("chandef->usedby: %d\n", chandef->usedby));
    
  switch(chandef->usedby)
  {
  case WANPIPE:
  case BRIDGE_NODE:

    //check what was in the parsed file:
    if(interface_file_reader->if_config.gateway[0] != '\0'){
      chandef->chanconf->gateway = 1;
    }else{
      chandef->chanconf->gateway = 0;
    }
  
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", IP_SETUP);
    menu_str += tmp_buff;

    if( strlen(interface_file_reader->if_config.ipaddr) <  MIN_IP_ADDR_STR_LEN ||
        strlen(interface_file_reader->if_config.point_to_point_ipaddr) < MIN_IP_ADDR_STR_LEN){

      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"IP Address Setup---> Incomplete\" ");
    }else{
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"IP Address Setup---> Complete\" ");
    }
    menu_str += tmp_buff;
    break;

  case TTY:
    //display Minor Number for the interface
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", TTY_MINOR_NUMBER);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"Minor Number-------> %s\" ", chandef->chanconf->addr);
    menu_str += tmp_buff;
    break;

  case TDM_VOICE:
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", TDMV_SPAN_NUMBER);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"TDM Voice Span-----------------> %d\" ", chandef->chanconf->spanno);
    menu_str += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", TDMV_ECHO_OPTIONS);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"Override Asterisk Echo Enable -> %s\" ",
	(chandef->chanconf->tdmv_echo_off == WANOPT_YES ? "Yes" : "No"));
    menu_str += tmp_buff;
    break;
  
  default:
    break;
  }

  //////////////////////////////////////////////////////////////////////////////////////
  snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", EMPTY_LINE);
  menu_str += tmp_buff;
  snprintf(tmp_buff, MAX_PATH_LENGTH, " \" \" ");
  menu_str += tmp_buff;
 
  if(chandef->usedby != TTY){
    //////////////////////////////////////////////////////////////////////////////////////
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", MISC_OPTIONS);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"Advanced options\" ");
    menu_str += tmp_buff;
  }

  ///////////////////////////////////////////////////////////////////////////////////////
  //create the explanation text for the menu
  protocol = chandef->chanconf->config_id;

  Debug(DBG_MENU_NET_INTERFACE_SETUP, ("creating explanation string\n"));

  if(protocol == WANCONFIG_FR){
    snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n------------------------------------------\
\nInterface: %s (bound to DLCI: %s)",
    chandef->name, chandef->addr);
  }else{
    snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n------------------------------------------\
\nParameters for interface: %s",
    chandef->name);
  }

  Debug(DBG_MENU_NET_INTERFACE_SETUP, ("explanation string: %s\n", tmp_buff));

  if(set_configuration(   YES,//indicates to call V2 of the function
                          MENU_BOX_BACK,//MENU_BOX_SELECT,
                          lxdialog_path,
                          "INTERFACE CONFIGURATION",
                          WANCFG_PROGRAM_NAME,
                          tmp_buff,
                          MENU_HEIGTH, MENU_WIDTH,
                          6,//number of items in the menu, including the empty lines
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

  Debug(1, ("net_if_setup: *selection_index: %d\n", *selection_index));

  exit_dialog = NO;
  switch(*selection_index)
  {
  case MENU_BOX_BUTTON_SELECT:
    Debug(DBG_MENU_NET_INTERFACE_SETUP,
      ("net_if_setup: option selected for editing: %s\n", get_lxdialog_output_string()));

    switch(atoi(get_lxdialog_output_string()))
    {
    case IF_NAME://interface name
      /////////////////////////////////////////////////////////////
show_if_name_input_box:
      snprintf(explanation_text, MAX_PATH_LENGTH, "Please specify a Network Interface Name");
      snprintf(initial_text, MAX_PATH_LENGTH, "%s", chandef->name);

      inb.set_configuration(  lxdialog_path,
                              backtitle,
                              explanation_text,
                              INPUT_BOX_HIGTH,
                              INPUT_BOX_WIDTH,
                              initial_text);

      inb.show(selection_index);

      switch(*selection_index)
      {
      case INPUT_BOX_BUTTON_OK:
        Debug(DBG_MENU_NET_INTERFACE_SETUP,
          ("Interface name on return: %s\n", inb.get_lxdialog_output_string()));

        name_len = strlen(inb.get_lxdialog_output_string());

        if( name_len < 1 || name_len > WAN_IFNAME_SZ){

          tb.show_error_message(lxdialog_path, NO_PROTOCOL_NEEDED,
"Invalid Interface name.\n\
%s\n\
Minimum name length is %d, maximum is %d characters.\n",
net_if_name_help_str,
1,
WAN_IFNAME_SZ
);
          goto show_if_name_input_box;
        }else{
          snprintf(chandef->name, WAN_IFNAME_SZ, inb.get_lxdialog_output_string());
          //list_el_chan_def->data.name
          Debug(DBG_MENU_NET_INTERFACE_SETUP,("menu_net_interface_setup: chan_def: %p\n", chandef));
        }
        break;

      case INPUT_BOX_BUTTON_HELP:

        tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, net_if_name_help_str);
        goto show_if_name_input_box;
        
      }//switch(*selection_index)
      /////////////////////////////////////////////////////////////
      break;

    case OPERATION_MODE://Operational Mode
      {
        menu_net_interface_operation_mode
          net_interface_operation_mode(lxdialog_path, list_element_logical_ch);

        if(net_interface_operation_mode.run(selection_index) == NO){
          rc = NO;
          exit_dialog = YES;
        }else{
	  //printf("1. chan_def->data.chanconf: 0x%p\n", chan_def->data.chanconf);
	  //check for TDM_VOICE
	  /*
	  if(get_used_by_integer_value(chan_def->data.usedby) == TDM_VOICE){
	    chan_def->data.chanconf->hdlc_streaming = WANOPT_NO;
	  }else{
	    chan_def->data.chanconf->hdlc_streaming = WANOPT_YES;
	  }
	  */
	}
      }
      break;
      
    case IP_SETUP://IP address
      {
        menu_net_interface_ip_configuration
          net_interface_ip_configuration(lxdialog_path, list_element_logical_ch);

        if(net_interface_ip_configuration.run(selection_index, *interface_file_reader) == NO){
          rc = NO;
          exit_dialog = YES;
        }else{
          if(interface_file_reader->create_net_interface_file(
                              &interface_file_reader->if_config) == NO){
            Debug(DBG_MENU_NET_INTERFACE_SETUP, ("create_net_interface_file() FAILED!!\n"));
            return NO;
          }
        }
      }
      break;

    case TTY_MINOR_NUMBER:
      //number between 0 and 31, including.
      int tty_minor_no;
      snprintf(explanation_text, MAX_PATH_LENGTH, "TTY Minor Number (between 0 and 31)");
      snprintf(initial_text, MAX_PATH_LENGTH, "%s", chandef->chanconf->addr);

show_tty_minor_no_input_box:
      inb.set_configuration(  lxdialog_path,
                              backtitle,
                              explanation_text,
                              INPUT_BOX_HIGTH,
                              INPUT_BOX_WIDTH,
                              initial_text);

      inb.show(selection_index);

      switch(*selection_index)
      {
      case INPUT_BOX_BUTTON_OK:
        Debug(DBG_MENU_NET_INTERFACE_SETUP,
          ("minor_no on return: %s\n", inb.get_lxdialog_output_string()));

        tty_minor_no = atoi(remove_spaces_in_int_string(inb.get_lxdialog_output_string()));

        if(tty_minor_no < 0 || tty_minor_no > 31){

          tb.show_error_message(lxdialog_path, NO_PROTOCOL_NEEDED,
                                "Invalid TTY Minor Number. Min: %d, Max: %d.",
                                0, 31);
          goto show_tty_minor_no_input_box;
        }else{
          snprintf( chandef->chanconf->addr, WAN_ADDRESS_SZ, "%d", tty_minor_no);
        }
        break;

      case INPUT_BOX_BUTTON_HELP:

        tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED,
          "Enter TTY Minor Number (between 0 and 31)");
        goto show_tty_minor_no_input_box;
      }//switch(*selection_index)
      break;
      
    case TDMV_SPAN_NUMBER:
      //number 1 and greater
      int tdm_spanno;
      snprintf(explanation_text, MAX_PATH_LENGTH, "TDM Voice Span Number (1 and greater)");
      snprintf(initial_text, MAX_PATH_LENGTH, "%d", chandef->chanconf->spanno);

show_tdm_spanno_input_box:
      inb.set_configuration(  lxdialog_path,
                              backtitle,
                              explanation_text,
                              INPUT_BOX_HIGTH,
                              INPUT_BOX_WIDTH,
                              initial_text);

      inb.show(selection_index);

      switch(*selection_index)
      {
      case INPUT_BOX_BUTTON_OK:
        Debug(DBG_MENU_NET_INTERFACE_SETUP,
          ("spanno on return: %s\n", inb.get_lxdialog_output_string()));

        tdm_spanno = atoi(remove_spaces_in_int_string(inb.get_lxdialog_output_string()));

        if(tdm_spanno < 1){

          tb.show_error_message(lxdialog_path, NO_PROTOCOL_NEEDED,
                                "Invalid TDMV Span Number. Min: 1");
          goto show_tdm_spanno_input_box;
        }else{
          chandef->chanconf->spanno = tdm_spanno;
        }
        break;

      case INPUT_BOX_BUTTON_HELP:

        tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED,
          "Enter TDMV Span Number (1 and greater)");
        goto show_tdm_spanno_input_box;
      }//switch(*selection_index)
      break;

    case TDMV_ECHO_OPTIONS:
      snprintf(tmp_buff, MAX_PATH_LENGTH, "Do you want to %s Echo Cancel option?",
	(chandef->chanconf->tdmv_echo_off == WANOPT_NO ? "Enable" : "Disable"));

      if(yes_no_question(   selection_index,
                            lxdialog_path,
                            NO_PROTOCOL_NEEDED,
                            tmp_buff) == NO){
        return NO;
      }

      switch(*selection_index)
      {
      case YES_NO_TEXT_BOX_BUTTON_YES:
        if(chandef->chanconf->tdmv_echo_off == WANOPT_NO){
          //was disabled - enable
          chandef->chanconf->tdmv_echo_off = WANOPT_YES;
        }else{
          //was enabled - disable
          chandef->chanconf->tdmv_echo_off = WANOPT_NO;
        }
        break;
      }
      break;
      
    case MISC_OPTIONS:
      {
        menu_net_interface_miscellaneous_options
          net_interface_miscellaneous_options(lxdialog_path, list_element_logical_ch);

        if(net_interface_miscellaneous_options.run(selection_index) == NO){
          rc = NO;
          exit_dialog = YES;
        }
      }
      break;

    case EMPTY_LINE:
      break;
      
    default:
      ERR_DBG_OUT(("Invalid option selected for editing!! selection: %s\n",
        get_lxdialog_output_string()));
      rc = NO;
      exit_dialog = YES;
    }
    break;

  case MENU_BOX_BUTTON_HELP:

    switch(atoi(get_lxdialog_output_string()))
    {
    case IF_NAME:
      tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, net_if_name_help_str);
      break;

    case OPERATION_MODE:
      tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, operation_mode_help_str);
      break;

    case IP_SETUP:
      tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, ip_help_str);
      break;

    case MISC_OPTIONS:
      tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, net_if_miscellaneous_help_str);
      break;

    default:
      tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, option_not_implemented_yet_help_str);
    }
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

