/***************************************************************************
                          menu_select_protocol.cpp  -  description
                             -------------------
    begin                : Mon December  6 2004
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

#include "menu_select_protocol.h"
#include "text_box.h"

#define DBG_MENU_SELECT_PROTOCOL 1

menu_select_protocol::menu_select_protocol( IN char * lxdialog_path,
					    IN conf_file_reader* cfr)
{
  Debug(DBG_MENU_SELECT_PROTOCOL, ("menu_select_protocol::menu_select_protocol()\n"));

  snprintf(this->lxdialog_path, MAX_PATH_LENGTH, "%s", lxdialog_path);
 
  //If set to NULL, the protocol in question will run in LIP layer,
  //and hardware type is not important. But only protocols implemented in LIP are valid.
  //Otherwize, display protocols valid for hardware/port.
  this->cfr = cfr;
}

menu_select_protocol::~menu_select_protocol()
{
  Debug(DBG_MENU_SELECT_PROTOCOL, ("menu_select_protocol::~menu_select_protocol()\n"));
}

int menu_select_protocol::run(OUT int * selected_protocol)
{
  string menu_str;
  int rc, number_of_items;
  char tmp_buff[MAX_PATH_LENGTH];
  //help text box
  text_box tb;

  link_def_t * link_def;
  wandev_conf_t *linkconf;

  Debug(DBG_MENU_SELECT_PROTOCOL, ("menu_select_protocol::run()\n"));

again:
  rc = YES;
  menu_str = " ";

  if(cfr != NULL){
    link_def = cfr->link_defs;
    linkconf = cfr->link_defs->linkconf;

    number_of_items = form_protocol_list_valid_for_hardware(  menu_str,
					    linkconf->card_type,
					    link_def->card_version,
					    linkconf->comm_port);
  }else{
    number_of_items = form_protocol_list_valid_for_lip_layer(menu_str);
  }
 
  if(number_of_items == 0){
    //something went wrong
    return NO;
  } 
 
  //dont display more than 8 items in the menu
  if(number_of_items > 8){
    number_of_items = 8;
  } 

  snprintf(tmp_buff, MAX_PATH_LENGTH, "Please select a Protocol.");

  //snprintf(&tmp_buff[strlen(tmp_buff)], MAX_PATH_LENGTH, "%s", MENUINSTR_EXIT);

  if(set_configuration(   YES,//indicates to call V2 of the function
                          MENU_BOX_BACK,
                          lxdialog_path,
                          "PROTOCOL SELECTION",
                          WANCFG_PROGRAM_NAME,
                          tmp_buff,
                          MENU_HEIGTH, MENU_WIDTH,
                          number_of_items,//8,//MAX_NUM_OF_VISIBLE_ITEMS_IN_MENU,
                          (char*)menu_str.c_str()
                          ) == NO){
    rc = NO;
    return rc;
  }

  int selection_index;
  if(show(&selection_index) == NO){
    return NO;
  }

  switch(selection_index)
  {
  case MENU_BOX_BUTTON_SELECT:
    Debug(DBG_MENU_SELECT_PROTOCOL, ("option selected: %s\n",
	 get_lxdialog_output_string()));

    *selected_protocol = atoi(get_lxdialog_output_string());
    break;

  case MENU_BOX_BUTTON_HELP:
    tb.show_help_message(lxdialog_path, NO_PROTOCOL_NEEDED, option_not_implemented_yet_help_str);
    goto again;

  case MENU_BOX_BUTTON_BACK:
    //exit the dialog
    break;
  }
  
  return rc;
}

//some protocols are ONLY in LIP layer
int menu_select_protocol::form_protocol_list_valid_for_lip_layer(OUT string& menu_str)
{
  int num_of_items = 0;
  char tmp_buff[MAX_PATH_LENGTH];
  conf_file_reader* local_cfr = (conf_file_reader*)global_conf_file_reader_ptr;
  
  if(local_cfr->link_defs->linkconf->card_type != WANOPT_ADSL){

    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_MPPP);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_MPPP));
    menu_str += tmp_buff;
    num_of_items++;
  
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_MPCHDLC);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_MPCHDLC));
    menu_str += tmp_buff;
    num_of_items++;

    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_MFR);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_MFR));
    menu_str += tmp_buff;
    num_of_items++;
  }

#if defined(__LINUX__) && !BSD_DEBG
  snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_TTY);
  menu_str += tmp_buff;
  snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_TTY));
  menu_str += tmp_buff;
  num_of_items++;
#endif

  return num_of_items;
}

//some protocols run directly in hadrdware, some will need creation of the LIP layer.
int menu_select_protocol::form_protocol_list_valid_for_hardware(string& menu_str,
								int card_type,
								int card_version,
								int comm_port)
{
  int num_of_items = 0;
  char tmp_buff[MAX_PATH_LENGTH];

  if(card_type != WANOPT_ADSL){ 
    num_of_items += form_protocol_list_valid_for_lip_layer(menu_str);
  }
 
  switch(card_type)
  {
  case WANOPT_S50X:
#if defined(__LINUX__) && !BSD_DEBG
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_HDLC);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_HDLC));
    menu_str += tmp_buff;

    num_of_items += 1;
#endif
    break;
    
  case WANOPT_S51X:
#if defined(__LINUX__) && !BSD_DEBG
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_HDLC);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_HDLC));
    menu_str += tmp_buff;
    switch(comm_port)
    {
    case WANOPT_PRI:
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_EDUKIT);
      menu_str += tmp_buff;
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_EDUKIT));
      menu_str += tmp_buff;
      break;
    }
    num_of_items += 2;
#endif
    break;
    
  case WANOPT_ADSL:
    //valid protocols depend on Encapsulation Mode
    num_of_items += form_protocol_list_valid_for_ADSL(menu_str);
    break;

  case WANOPT_AFT:
    switch(card_version)
    {
    case A101_ADPTR_1TE1://WAN_MEDIA_T1:
    case A104_ADPTR_4TE1:
#if defined(__LINUX__) && !BSD_DEBG
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_HDLC);
      menu_str += tmp_buff;
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_HDLC));
      menu_str += tmp_buff;
#endif
      //the 'TDM_VOICE' should be displayed as protocol, not 'operational mode'
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", PROTOCOL_TDM_VOICE);
      menu_str += tmp_buff;
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(PROTOCOL_TDM_VOICE));
      menu_str += tmp_buff;
           
      num_of_items += 2;
      break;

#if defined(__LINUX__) && !BSD_DEBG
    case A300_ADPTR_U_1TE3://WAN_MEDIA_DS3:
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_HDLC);
      menu_str += tmp_buff;
      snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_HDLC));
      menu_str += tmp_buff;
      
      num_of_items += 1;
      break;
#endif
    }
    break;
    
  default:
    ERR_DBG_OUT(("Invalid card type: %d!\n", card_type));
  }

  return num_of_items;
}

//return number of added items
int menu_select_protocol::form_protocol_list_valid_for_ADSL(string& menu_str)
{
  char tmp_buff[MAX_PATH_LENGTH];
  link_def_t * link_def;
  wandev_conf_t *linkconf;
  wan_adsl_conf_t* adsl_cfg;
  int local_item_count = 0;

  link_def = cfr->link_defs;
  linkconf = cfr->link_defs->linkconf;
  adsl_cfg = &linkconf->u.adsl;

  switch(adsl_cfg->EncapMode)
  {
  case RFC_MODE_BRIDGED_ETH_LLC:
  case RFC_MODE_BRIDGED_ETH_VC:
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", PROTOCOL_ETHERNET);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", ADSL_ETHERNET_STR);
    menu_str += tmp_buff;
    local_item_count++;
    break;
    
  case RFC_MODE_ROUTED_IP_LLC:
  case RFC_MODE_ROUTED_IP_VC:
  case RFC_MODE_RFC1577_ENCAP:
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", PROTOCOL_IP);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", ADSL_IP_STR);
    menu_str += tmp_buff;
    local_item_count++;
    break;
    
  case RFC_MODE_PPP_LLC:
  case RFC_MODE_PPP_VC:
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_TTY);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_TTY));
    menu_str += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%d\" ", WANCONFIG_MPPP);
    menu_str += tmp_buff;
    snprintf(tmp_buff, MAX_PATH_LENGTH, " \"%s\" ", get_protocol_string(WANCONFIG_MPPP));
    menu_str += tmp_buff;
 
    local_item_count += 2;
    break;
  }
  return local_item_count;
}

