/***************************************************************************
                          conf_file_writer.cpp  -  description
                             -------------------
    begin                : Fri Apr 2 2004
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

#include "conf_file_writer.h"

void print_look_up_t_table(look_up_t* table);
void print_key_word_t_table(key_word_t* table);

extern key_word_t common_conftab[];
extern look_up_t config_id_str[];
extern key_word_t fr_conftab[];
extern key_word_t ppp_conftab[];
extern key_word_t chdlc_conftab[];
extern key_word_t chan_conftab[];
extern key_word_t adsl_conftab[];

#define DBG_CONF_FILE_WRITER 1

enum {
  FORM_INTERFACES_SECTION,
  FORM_PROFILE_SECTION
};

int current_profile_number = 55;

//////////////////////////////////////////////////////////////
//Tables for converting integer values to string values
//NOTE: each integer value in a table MUST BE UNIQUE.
look_up_t	card_type_table[] =
{
	{ WANOPT_S50X,		(void*)"S50X" },
	{ WANOPT_S51X,		(void*)"S51X" },
	{ WANOPT_ADSL,		(void*)"S518" },
	{ WANOPT_AFT,		(void*)"AFT"  },
	{ 0,			NULL	      }
};

look_up_t	yes_no_options_table[] =
{
	{ WANOPT_YES,		(void*)"YES"	},
	{ WANOPT_NO,		(void*)"NO" 	},
	{ 0,			      NULL	}
};

look_up_t	physical_medium_options_table[] =
{
	{ WAN_MEDIA_NONE,    (void*)"None"   },
	{ WAN_MEDIA_T1,      (void*)"T1"     },
	{ WAN_MEDIA_E1,      (void*)"E1"     },
	{ WAN_MEDIA_56K,     (void*)"56K"    },
	{ WAN_MEDIA_DS3,	(void*)"DS3"	},
	{ WAN_MEDIA_E3,	  	(void*)"E3"  	},  
	{ 0,	                NULL	        }
};

look_up_t	te1_line_code_options_table[] =
{
  { WAN_LCODE_AMI,        (void*)"AMI"    },
  { WAN_LCODE_B8ZS,       (void*)"B8ZS"   },
  { WAN_LCODE_HDB3,       (void*)"HDB3"   },
  { WAN_LCODE_B3ZS,    (void*)"B3ZS"   },
  { 0,			              NULL		        }
};

look_up_t	te1_clock_mode_options_table[] =
{
  { WAN_NORMAL_CLK,   	(void*)"NORMAL" },
  { WAN_MASTER_CLK,   	(void*)"MASTER" },
	{ 0,			              NULL		        }
};

look_up_t te1_frame_options_table[] =
{
  { WAN_FR_D4,         (void*)"D4"       },
  { WAN_FR_ESF,        (void*)"ESF"      },
  { WAN_FR_NCRC4,      (void*)"NCRC4"    },
  { WAN_FR_CRC4,       (void*)"CRC4"     },
  { WAN_FR_UNFRAMED,   (void*)"UNFRAMED" },
  { WAN_FR_E3_G751,   	  (void*)"G.751" },
  { WAN_FR_E3_G832,       (void*)"G.832" },
  { WAN_FR_DS3_Cbit,      (void*)"C-BIT" },
  { WAN_FR_DS3_M13,       (void*)"M13" },
  { 0,			              NULL  }
};

look_up_t t1_line_build_out_options_table[] =
{
  { WAN_T1_LBO_0_DB,  	(void*)"0DB"           },
  { WAN_T1_LBO_75_DB,  (void*)"7.5DB"         },
  { WAN_T1_LBO_15_DB,  (void*)"15DB"          },
  { WAN_T1_LBO_225_DB, (void*)"22.5DB"        },
  { WAN_T1_0_110,  	  (void*)"0-110FT"       },
  { WAN_T1_110_220,  	(void*)"110-220FT"     },
  { WAN_T1_220_330,  	(void*)"220-330FT"     },
  { WAN_T1_330_440,  	(void*)"330-440FT"     },
  { WAN_T1_440_550,  	(void*)"440-550FT"     },
  { WAN_T1_550_660,  	(void*)"550-660FT"     },
  { 0,		              NULL               }
};

look_up_t serial_interface_type_options_table[] =
{
	{ WANOPT_RS232,	(void*)"RS232"	},
	{ WANOPT_V35,		(void*)"V35"		},
	{ 0,			              NULL	  }
};

look_up_t serial_clock_type_options_table[] =
{
	{ WANOPT_EXTERNAL,	(void*)"EXTERNAL"	},
	{ WANOPT_INTERNAL,	(void*)"INTERNAL"	},
	{ 0,			              NULL	        }
};

look_up_t frame_relay_in_channel_signalling_options_table[] =
{
	/*----- Frame relay in-channel signalling */
	{ WANOPT_FR_AUTO_SIG,	(void*)"AUTO"		},
	{ WANOPT_FR_ANSI,	(void*)"ANSI"		},
	{ WANOPT_FR_Q933,	(void*)"Q933"		},
	{ WANOPT_FR_LMI,	(void*)"LMI"		},
	{ 0,			NULL	    		}
};

look_up_t frame_relay_station_type_options_table[] =
{
	{ WANOPT_CPE,		(void*)"CPE"		},
	{ WANOPT_NODE,		(void*)"NODE"		},
	{ 0,			              NULL	    }
};

look_up_t commport_type_options_table[] =
{
	{ WANOPT_PRI,           (void*)"PRI"  },
	{ WANOPT_SEC,           (void*)"SEC"  },
	{ 0,			              NULL	        }
};

look_up_t ppp_ip_mode_options_table[] =
{
	/*----- PPP IP Mode Options -----------*/
	{ WANOPT_PPP_STATIC,	(void*)"STATIC"	},
	{ WANOPT_PPP_HOST,	(void*)"HOST"		},
	{ WANOPT_PPP_PEER,	(void*)"PEER"		},
	{ 0,			              NULL	        }
};

look_up_t connection_type_options_table[] =
{
	/*----- Connection options ------------*/
	{ WANOPT_PERMANENT,	(void*)"PERMANENT"	},
	{ WANOPT_SWITCHED,	(void*)"SWITCHED"	},
	{ WANOPT_ONDEMAND,	(void*)"ONDEMAND"	},
	{ 0,			              NULL	        }
};

look_up_t data_encoding_options_table[] =
{
	/*----- Data encoding -----------------*/
	{ WANOPT_NRZ,		(void*)"NRZ"		},
	{ WANOPT_NRZI,	(void*)"NRZI"		},
	{ WANOPT_FM0,		(void*)"FM0"		},
	{ WANOPT_FM1,		(void*)"FM1"		},
	{ 0,			              NULL	  }
};

look_up_t line_idle_options_table[] =
{
	/*----- Idle Line ----------------------*/
	{ WANOPT_IDLE_FLAG,	(void*)"FLAG"	},
	{ WANOPT_IDLE_MARK,	(void*)"MARK"	},
	{ 0,			              NULL	  }
};

look_up_t protocol_options_table[] =
{
	{ WANCONFIG_MPPP,	(void*)"MP_PPP"	  },
	{ WANCONFIG_MPCHDLC,	(void*)"MP_CHDLC" },
	{ WANCONFIG_MFR, 	(void*)"MP_FR"	  },
	//{ WANCONFIG_TTY, 	(void*)"TTY"	  },
	{ WANCONFIG_TTY, 	(void*)"NONE"	  },//must be saved as none!!!
	//{ WANOPT_NO,		(void*)"RAW"	  },
	{ WANOPT_NO,		(void*)"NONE"	  },
	{ WANCONFIG_HDLC,	(void*)"HDLC"	  },
	{ WANCONFIG_EDUKIT,	(void*)"EDUKIT"	  },
	{ WANCONFIG_PPP,	(void*)"PPP"	  },
	{ WANCONFIG_CHDLC,	(void*)"CHDLC"	  },
	{ 0,			NULL		  }
};

look_up_t te3_rdevice_type_options_table[] =
{
	{WAN_TE3_RDEVICE_NONE,		(void*)"None"	},
	{WAN_TE3_RDEVICE_ADTRAN,	(void*)"ADTRAN"	},
	{WAN_TE3_RDEVICE_DIGITALLINK,	(void*)"DIGITAL-LINK"},
	{WAN_TE3_RDEVICE_KENTROX,	(void*)"KENTROX"},
	{WAN_TE3_RDEVICE_LARSCOM,	(void*)"LARSCOM"},
	{WAN_TE3_RDEVICE_VERILINK,	(void*)"VERILINK"},
	{ 0,			              NULL	}
};

look_up_t adsl_encapsulation_options_table[] =
{
	{ RFC_MODE_BRIDGED_ETH_LLC,	(void*)"ETH_LLC_OA" },
	{ RFC_MODE_BRIDGED_ETH_VC,	(void*)"ETH_VC_OA"  },
	{ RFC_MODE_ROUTED_IP_LLC,	(void*)"IP_LLC_OA"  },
	{ RFC_MODE_ROUTED_IP_VC,	(void*)"IP_VC_OA"   },
	{ RFC_MODE_PPP_LLC,		(void*)"PPP_LLC_OA" },
	{ RFC_MODE_PPP_VC,		(void*)"PPP_VC_OA"  },
	{ 0,			        NULL		    }
};

look_up_t adsl_standard_options_table[] =
{
	{ WANOPT_ADSL_T1_413,                   (void*)"ADSL_T1_413"      },
	{ WANOPT_ADSL_G_LITE,                   (void*)"ADSL_G_LITE"      },
	{ WANOPT_ADSL_G_DMT,                    (void*)"ADSL_G_DMT"       },
	{ WANOPT_ADSL_ALCATEL_1_4,              (void*)"ADSL_ALCATEL_1_4" },
	{ WANOPT_ADSL_ALCATEL,                  (void*)"ADSL_ALCATEL"     },
	{ WANOPT_ADSL_MULTIMODE,                (void*)"ADSL_MULTIMODE"   },
	{ WANOPT_ADSL_T1_413_AUTO,              (void*)"ADSL_T1_413_AUTO" },
	{ WANOPT_ADSL_ADI,			(void*)"ADI"		  },
	{ 0,			        NULL				  }
};

look_up_t adsl_trellis_options_table[] =
{
	{ WANOPT_ADSL_TRELLIS_ENABLE,           (void*)"ADSL_TRELLIS_ENABLE"           },
	{ WANOPT_ADSL_TRELLIS_DISABLE,          (void*)"ADSL_TRELLIS_DISABLE"          },
	{ WANOPT_ADSL_TRELLIS_LITE_ONLY_DISABLE,(void*)"ADSL_TRELLIS_LITE_ONLY_DISABLE"},
	{ 0,			        NULL				  }
};

look_up_t adsl_coding_gain_options_table[] =
{
	{ WANOPT_ADSL_0DB_CODING_GAIN,          (void*)"ADSL_0DB_CODING_GAIN"          },
	{ WANOPT_ADSL_1DB_CODING_GAIN,          (void*)"ADSL_1DB_CODING_GAIN"          },
	{ WANOPT_ADSL_2DB_CODING_GAIN,          (void*)"ADSL_2DB_CODING_GAIN"          },
	{ WANOPT_ADSL_3DB_CODING_GAIN,          (void*)"ADSL_3DB_CODING_GAIN"          },
	{ WANOPT_ADSL_4DB_CODING_GAIN,          (void*)"ADSL_4DB_CODING_GAIN"          },
	{ WANOPT_ADSL_5DB_CODING_GAIN,          (void*)"ADSL_5DB_CODING_GAIN"          },
	{ WANOPT_ADSL_6DB_CODING_GAIN,          (void*)"ADSL_6DB_CODING_GAIN"          },
	{ WANOPT_ADSL_7DB_CODING_GAIN,          (void*)"ADSL_7DB_CODING_GAIN"          },
	{ WANOPT_ADSL_AUTO_CODING_GAIN,         (void*)"ADSL_AUTO_CODING_GAIN"         },
	{ 0,			        NULL				  }
};

look_up_t adsl_bin_adjust_options_table[] =
{
	{ WANOPT_ADSL_RX_BIN_DISABLE,           (void*)"ADSL_RX_BIN_DISABLE"           },
	{ WANOPT_ADSL_RX_BIN_ENABLE,            (void*)"ADSL_RX_BIN_ENABLE"            },
	{ 0,			        NULL				  }
};

look_up_t adsl_framing_struct_options_table[] =
{
	{ WANOPT_ADSL_FRAMING_TYPE_0,           (void*)"ADSL_FRAMING_TYPE_0"           },
	{ WANOPT_ADSL_FRAMING_TYPE_1,           (void*)"ADSL_FRAMING_TYPE_1"           },
	{ WANOPT_ADSL_FRAMING_TYPE_2,           (void*)"ADSL_FRAMING_TYPE_2"           },
	{ WANOPT_ADSL_FRAMING_TYPE_3,           (void*)"ADSL_FRAMING_TYPE_3"           },
	{ 0,			        NULL				  }
};

look_up_t adsl_exchange_options_table[] =
{
	{ WANOPT_ADSL_EXPANDED_EXCHANGE,        (void*)"ADSL_EXPANDED_EXCHANGE"        },
	{ WANOPT_ADSL_SHORT_EXCHANGE,           (void*)"ADSL_SHORT_EXCHANGE"           },
	{ 0,			        NULL				  }
};

look_up_t adsl_clock_type_options_table[] =
{
	{ WANOPT_ADSL_CLOCK_CRYSTAL,            (void*)"ADSL_CLOCK_CRYSTAL"            },
	{ WANOPT_ADSL_CLOCK_OSCILLATOR,         (void*)"ADSL_CLOCK_OSCILLATOR"         },
	{ 0,			        NULL				  }
};
//////////////////////////////////////////////////////////////

conf_file_writer::conf_file_writer(IN conf_file_reader* cfr)
{
  Debug(DBG_CONF_FILE_WRITER, ("conf_file_writer::conf_file_writer()\n"));
  this->cfr = cfr;
}

conf_file_writer::~conf_file_writer()
{
  Debug(DBG_CONF_FILE_WRITER, ("conf_file_writer::~conf_file_writer()\n"));
}

int conf_file_writer::write()
{
  Debug(DBG_CONF_FILE_WRITER, ("conf_file_writer::write()\n"));
  char tmp_buff[MAX_PATH_LENGTH];
  string top_comment_str;
  string devices_section_str;
  string interfaces_section_str;
  string wanpipe_section_str;
  string wp_interface;
  

  objects_list * main_obj_list = cfr->main_obj_list;

  //Top comment string. Contains name of the Wan Device, time of creation etc.
  top_comment_str = "";

  //[devices] section string
  devices_section_str = "";

  //[interfaces] section string
  interfaces_section_str = "";

  //[wanpipe??] section string
  wanpipe_section_str = "";

  //per-interface string. call for each interface. on return add to the 'main_conf_str'.
  wp_interface = "";

  //form full path to the conf file we want to write
  full_file_path = wanpipe_cfg_dir;

  full_file_path += cfr->link_defs->name;
  full_file_path += ".conf";

  ///////////////////////////////////////////////////////
  if(form_top_comment_str(top_comment_str) == NO){
    return NO;
  }
  //the comment should be 'written', all the rest should be 'appended' to file.
  if(write_string_to_file((char*)full_file_path.c_str(),
                          (char*)top_comment_str.c_str()) == NO){
    return NO;
  }

  ///////////////////////////////////////////////////////
  if(form_devices_section_str(devices_section_str) == NO){
    return NO;
  }

  if(append_string_to_file((char*)full_file_path.c_str(),
                          (char*)devices_section_str.c_str()) == NO){
    return NO;
  }

  ///////////////////////////////////////////////////////
  snprintf(tmp_buff, MAX_PATH_LENGTH,"\n[interfaces]\n");
  interfaces_section_str = tmp_buff;

  traverse_interfaces(interfaces_section_str, main_obj_list,
		  FORM_INTERFACES_SECTION);
  
  if(append_string_to_file((char*)full_file_path.c_str(),
                          (char*)interfaces_section_str.c_str()) == NO){
    return NO;
  }

  //////////////////////////////////////////////////////
  //i.g.: [wanpipe1]
  snprintf(tmp_buff, MAX_PATH_LENGTH,"\n[%s]\n", cfr->link_defs->name);
  wanpipe_section_str = tmp_buff;

  if(form_wanpipe_section_str(wanpipe_section_str) == NO){
    return NO;
  }

  if(append_string_to_file((char*)full_file_path.c_str(),
                          (char*)wanpipe_section_str.c_str()) == NO){
    return NO;
  }

  //////////////////////////////////////////////////////
  //[profile]. i.g.: [fr.fr2]
  interfaces_section_str = "";
  traverse_interfaces(interfaces_section_str, main_obj_list,
		  FORM_PROFILE_SECTION);
  
  return YES;
}

int conf_file_writer::form_top_comment_str(string& top_comment_str)
{
  char tmp_buff[MAX_PATH_LENGTH];

  str_toupper(cfr->link_defs->name);

  snprintf(tmp_buff, MAX_PATH_LENGTH,
"#================================================\n\
# %s Configuration File\n\
#================================================\n\
#\n\
# Date: %s\
#\n\
# Note: This file was generated automatically\n\
#       by /usr/sbin/%s program.\n\
#\n\
#       If you want to edit this file, it is\n\
#       recommended that you use wancfg program\n\
#       to do so.\n\
#================================================\n\
# Sangoma Technologies Inc.\n\
#================================================\n",
cfr->link_defs->name,
get_date_and_time(),
WANCFG_EXECUTABLE_NAME
);

  str_tolower(cfr->link_defs->name);

  top_comment_str = tmp_buff;

  return YES;
}

//[devices]
//wanpipe2 = WAN_FR, Comment
int conf_file_writer::form_devices_section_str(string& devices_section_str)
{
  char tmp_buff[MAX_PATH_LENGTH];
  int config_id = cfr->link_defs->linkconf->config_id;
  int card_version = cfr->link_defs->card_version;
	  
  //check that 'config_id' is in the table.
  if(get_keyword_from_look_up_t_table(config_id_str, config_id) == NULL){
    ERR_DBG_OUT(("Invalid 'config_id' (%d) passed for saving to file!!\n",
      		config_id));
    return NO;
  }

  switch(config_id)
  {
  case WANCONFIG_HDLC:	//for 'HDLC streaming' on S514
    //even for  'HDLC streaming' it should be WANCONFIG_MPROT !
    snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n\
[devices]\n\
%s = %s, %s\n",
cfr->link_defs->name,
get_keyword_from_look_up_t_table(config_id_str, WANCONFIG_MPROT),
(cfr->link_defs->descr == NULL ? "Comment" : cfr->link_defs->descr)
);
    break;
    
  case WANCONFIG_MPROT:	//for all "LIP" layer protocols on S514
  case WANCONFIG_EDUKIT://for EduKit on S514
  case WANCONFIG_ADSL:  
    snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n\
[devices]\n\
%s = %s, %s\n",
cfr->link_defs->name,
get_keyword_from_look_up_t_table(config_id_str, config_id),
(cfr->link_defs->descr == NULL ? "Comment" : cfr->link_defs->descr)
);
    break;

  case WANCONFIG_AFT:

    switch(card_version)
    {
    case A300_ADPTR_U_1TE3://WAN_MEDIA_DS3
	    
      snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n\
[devices]\n\
%s = %s, %s\n",
cfr->link_defs->name,
get_keyword_from_look_up_t_table(config_id_str, WANCONFIG_AFT_TE3),
(cfr->link_defs->descr == NULL ? "Comment" : cfr->link_defs->descr)
);
      break;
	
    case A101_ADPTR_1TE1:
      snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n\
[devices]\n\
%s = %s, %s\n",
cfr->link_defs->name,
get_keyword_from_look_up_t_table(config_id_str, WANCONFIG_AFT),	//for A101/2
(cfr->link_defs->descr == NULL ? "Comment" : cfr->link_defs->descr)
);
      break;

    case A104_ADPTR_4TE1:
      snprintf(tmp_buff, MAX_PATH_LENGTH,
"\n\
[devices]\n\
%s = %s, %s\n",
cfr->link_defs->name,
get_keyword_from_look_up_t_table(config_id_str, WANCONFIG_AFT_TE1),//for A104
(cfr->link_defs->descr == NULL ? "Comment" : cfr->link_defs->descr)
);
      break;
 
    default:
      ERR_DBG_OUT(("Unsupported 'card_version' (%d) passed for saving to file!!\n",
	card_version));
      return NO;
    }//switch()
    break;

  default:
    ERR_DBG_OUT(("Unsupported 'config_id' (%d) passed for saving to file!!\n",
      config_id));
    return NO;
  }

  Debug(DBG_CONF_FILE_WRITER, ("devices_section_str: %s\n", tmp_buff));
  devices_section_str += tmp_buff;
  return YES;
}

char* conf_file_writer::get_keyword_from_key_word_t_table(IN key_word_t* table,
                                                          IN int offset_in_structure)
{
  char* keyword;
  key_word_t* orig_table_ptr = table;

  Debug(0, ("get_keyword_from_key_word_t_table()\n"));

  keyword = table->keyword;
  while(keyword != NULL && table->offset != (unsigned int)offset_in_structure){
    //Debug(DBG_CONF_FILE_WRITER, ("keyword: %s\n", table->keyword));
    Debug(0, ("keyword: %s\n", table->keyword));
    table++;
    keyword = table->keyword;
  }

  if(keyword == NULL){
    printf("get_keyword_from_key_word_t_table(): failed to find value at offset: %d\n",
      offset_in_structure);
    print_key_word_t_table(orig_table_ptr);
    exit(1);
  }

  return keyword;
}

void print_key_word_t_table(key_word_t* table)
{
  char* keyword;

  printf("\nPrinting the searched table:\n");

  keyword = table->keyword;
  while(keyword != NULL){
    printf("keyword: %s at offset: %d\n", table->keyword, table->offset);
    table++;
    keyword = table->keyword;
  }
  printf("End of table.\n\n");
}

char* conf_file_writer::get_keyword_from_look_up_t_table(IN look_up_t* table,
                                                         IN int search_value)
{
  char* keyword;
  look_up_t* orig_table_ptr = table;

  Debug(DBG_CONF_FILE_WRITER, ("get_keyword_from_look_up_t_table()\n"));

  keyword = (char*)table->ptr;
  while(keyword != NULL && table->val != (unsigned int)search_value){
    //Debug(DBG_CONF_FILE_WRITER, ("keyword: %s\n", keyword));
    Debug(0, ("keyword: %s\n", keyword));
    table++;
    keyword = (char*)table->ptr;
  }

  if(keyword == NULL){
    printf("Failed to find value in one of the following:\n");
    print_look_up_t_table(orig_table_ptr);
    ERR_DBG_OUT(("Failed to find value: 0x%X.\n", search_value));
    exit(1);
  }
  return keyword;
}

void print_look_up_t_table(look_up_t* table)
{
  char* keyword;

  printf("\nPrinting the searched table:\n");

  keyword = (char*)table->ptr;
  while(keyword != NULL){
    Debug(1, ("%s\t-->%d\n", keyword, table->val));
    table++;
    keyword = (char*)table->ptr;
  }
  printf("End of table.\n\n");
}

int conf_file_writer::traverse_interfaces(string& interfaces_section_str,
					  objects_list * obj_list,
					  IN int task_type)
{
  char tmp_buff[MAX_PATH_LENGTH];
  
  chan_def_t* chandef;
  wanif_conf_t* chanconf;
  int config_id;
  int if_counter = 1;
  
  Debug(DBG_CONF_FILE_WRITER, ("traverse_interfaces()\n"));
  
  
  if(obj_list == NULL){
    ERR_DBG_OUT(("traverse_interfaces(): obj_list == NULL!!\n"));
    snprintf(tmp_buff, MAX_PATH_LENGTH, "traverse_interfaces(): obj_list == NULL!!\n");
    return NO;
  }

  list_element_chan_def* list_el_chan_def = (list_element_chan_def*)obj_list->get_first();
 	

  while(list_el_chan_def != NULL){
    chandef = &list_el_chan_def->data;
    chanconf = chandef->chanconf;
    config_id = chanconf->config_id;

    Debug(DBG_CONF_FILE_WRITER, ("device name->: %s\n",chandef->name ));
 

    switch(task_type)
    {
    case FORM_INTERFACES_SECTION:
      //has to use 'pre-traversing'
      if(form_interface_str(interfaces_section_str, list_el_chan_def) == NO){
        return NO;
      }
      break;
    }

    if(list_el_chan_def->next_objects_list != NULL){
      //profile number has the sequence number of curren 'group of channels' which
      //has LIP layer above it.	    
      current_profile_number = if_counter++;
      Debug(DBG_CONF_FILE_WRITER, ("++++++++++++ current_profile_number: %d\n",
			    current_profile_number));
      //recursive call
      traverse_interfaces(interfaces_section_str, 
		      (objects_list*)list_el_chan_def->next_objects_list, 
		      task_type);
    }

    switch(task_type)
    {
    case FORM_PROFILE_SECTION:
      //has to use 'post-traversing'
      if(chandef->usedby == STACK){
	
	//write profile for the level above this one
	if(form_profile_str(interfaces_section_str, list_el_chan_def) == NO){
	  return NO;
	}
	if(form_per_interface_str(interfaces_section_str, list_el_chan_def, 0) == NO){
	  return NO;
	}
	
      }else{
	switch(config_id)
	{
	case WANCONFIG_ADSL:
	case WANCONFIG_HDLC:
	case WANCONFIG_EDUKIT:
	case WANCONFIG_AFT:
	default:
	  if(form_per_interface_str(interfaces_section_str, list_el_chan_def, 0) == NO){
	    return NO;
	  }
	  break;
	}
      }
      break;
    }

    if(append_string_to_file((char*)full_file_path.c_str(),
                          (char*)interfaces_section_str.c_str()) == NO){
      return NO;
    }

    interfaces_section_str = "";

    list_el_chan_def = (list_element_chan_def*)obj_list->get_next_element(list_el_chan_def);
  }//while()
  
  return YES;
}

//wp2fr16 = wanpipe2, 16, WANPIPE, Comment
int conf_file_writer::form_interface_str(string& interfaces_section_str,
                                         list_element_chan_def* list_el_chan_def)
{
  char tmp_buff[MAX_PATH_LENGTH];
  chan_def_t* chandef = &list_el_chan_def->data;
  wanif_conf_t* chanconf = chandef->chanconf;
  int config_id = chanconf->config_id;
#if DBG_FLAG
  int protocol = chanconf->protocol;
#endif
  link_def_t* link_def = cfr->link_defs;

  Debug(DBG_CONF_FILE_WRITER, ("form_interface_str(): name: %s\n", chandef->name));
  Debug(DBG_CONF_FILE_WRITER, ("config_id: %d (%s)\n", config_id, get_protocol_string(config_id)));
  Debug(DBG_CONF_FILE_WRITER, ("protocol: %d (%s)\n", protocol, get_protocol_string(protocol)));
  Debug(DBG_CONF_FILE_WRITER, ("chandef->usedby: %s\n", get_used_by_string(chandef->usedby)));

  //part1: w3g1 = wanpipe3,
  snprintf(tmp_buff, MAX_PATH_LENGTH,
      "%s = %s,",
      chandef->name,
      link_def->name);
    interfaces_section_str += tmp_buff;

  //part2 for STACK 
  //if(protocol == 0){
  if(chandef->usedby == STACK){
     snprintf(tmp_buff, MAX_PATH_LENGTH,
	      " , %s, %s\n",
	      get_used_by_string(chandef->usedby),
	      (chandef->label == NULL ? "Comment" : chandef->label));
  
    interfaces_section_str += tmp_buff;
    return YES;
  }
  
  //part2 for non-STACK
  switch(config_id)
  {
  case WANCONFIG_ADSL:
  case WANCONFIG_HDLC:
  case WANCONFIG_EDUKIT:
  case PROTOCOL_TDM_VOICE:
  case WANCONFIG_AFT: //if TTY, config_id is set to 'WANCONFIG_AFT'
		      //because 'PROTOCOL' is expected (by the driver) to be set to 'NONE'
    //wp1edu = wanpipe1, , WANPIPE, Comment
    snprintf(tmp_buff, MAX_PATH_LENGTH,
      " , %s, %s\n",
      get_used_by_string(chandef->usedby),
      (chandef->label == NULL ? "Comment" : chandef->label));
      break;
  
  case WANCONFIG_MFR:
    snprintf(tmp_buff, MAX_PATH_LENGTH,
      //" %s, %s, %s, %s.%s%s\n",
      " %s, %s, %s, %s.%s%d\n",
      ((atoi(chandef->addr) == 1) ? "auto" : chandef->addr),
      get_used_by_string(chandef->usedby),
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      current_profile_number);
      //chandef->addr);
    break;

  case WANCONFIG_TTY:
    snprintf(tmp_buff, MAX_PATH_LENGTH,
      //" %s, %s, %s, %s.%s%s\n",
      " %s, %s, %s, %s.%s%d\n",
      chandef->chanconf->addr,
      get_used_by_string(chandef->usedby),
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      current_profile_number);
      //chandef->chanconf->addr);
      //chandef->addr);
    break;

  default:
    //for all others there the 'addr' is not used at all
    snprintf(tmp_buff, MAX_PATH_LENGTH,
      //" , %s, %s, %s.%s%s\n",
      " , %s, %s, %s.%s%d\n",
      get_used_by_string(chandef->usedby),
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      current_profile_number);
      //chandef->addr);
  }

  interfaces_section_str += tmp_buff;

  return YES;
}

int conf_file_writer::form_wanpipe_section_str(string& wanpipe_section_str)
{
  string tmp_string;

  tmp_string = "";
  if(form_wanpipe_card_location_str(tmp_string) == NO){
    return NO;
  }else{
    wanpipe_section_str += tmp_string;
  }

  tmp_string = "";
  switch(cfr->link_defs->linkconf->card_type)
  {
  case WANOPT_S51X:

    switch(cfr->link_defs->card_version)
    {
    case S5144_ADPTR_1_CPU_T1E1:
    case S5145_ADPTR_1_CPU_56K:

      if(form_fe_card_cfg_str(tmp_string) == NO){
        return NO;
      }else{
        wanpipe_section_str += tmp_string;
      }
      break;
    }
    break;

  case WANOPT_AFT:
     if(form_fe_card_cfg_str(tmp_string) == NO){
       return NO;
     }else{
       wanpipe_section_str += tmp_string;
     }
     break;

  case WANOPT_ADSL:
    //FIXME: write ADSL global cfg
    if(form_adsl_global_cfg_str(tmp_string) == NO){
      return NO;	
    }else{
       wanpipe_section_str += tmp_string;
    }
    break;
  }

  tmp_string = "";
  if(form_wanpipe_card_miscellaneous_options_str(tmp_string) == NO){
    return NO;
  }else{
    wanpipe_section_str += tmp_string;
  }
  
  return YES;
}

int conf_file_writer::form_adsl_global_cfg_str(string& tmp_string)
{
  wan_adsl_conf_t* adsl_cfg = &cfr->link_defs->linkconf->u.adsl;
  char* keyword;
  char tmp_buff[MAX_PATH_LENGTH];
  string adsl_global_cfg;

  Debug(DBG_CONF_FILE_WRITER, ("form_adsl_global_cfg_str()\n"));

  adsl_global_cfg = "";

  ///////////////////////////////////////////////////////////////////////////////////////
  //first non-advanced configuration. insert new line before it.
  adsl_global_cfg += "\n";
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, EncapMode));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'EncapMode'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'EncapMode': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
    get_keyword_from_look_up_t_table( adsl_encapsulation_options_table,
				      adsl_cfg->EncapMode));
  adsl_global_cfg += tmp_buff;
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, atm_autocfg));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'atm_autocfg'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'atm_autocfg': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
    get_keyword_from_look_up_t_table( yes_no_options_table,
				      adsl_cfg->atm_autocfg));
  adsl_global_cfg += tmp_buff; 
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, Vci));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'Vci'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'Vci': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%u\n", adsl_cfg->Vci);
  adsl_global_cfg += tmp_buff;
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, Vpi));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'Vpi'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'Vpi': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%u\n", adsl_cfg->Vpi);
  adsl_global_cfg += tmp_buff;
 
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, atm_watchdog));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'atm_watchdog'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'atm_watchdog': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
    get_keyword_from_look_up_t_table( yes_no_options_table,
				      adsl_cfg->atm_watchdog));
  adsl_global_cfg += tmp_buff; 

  ///////////////////////////////////////////////////////////////////////////////////////
  //advanced options
  adsl_global_cfg += "\n";
  
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, Verbose));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'Verbose'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'Verbose': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", adsl_cfg->Verbose);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, RxBufferCount));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'RxBufferCount'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'RxBufferCount': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%u\n", adsl_cfg->RxBufferCount);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, TxBufferCount));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'TxBufferCount'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'TxBufferCount': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%u\n", adsl_cfg->TxBufferCount);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, Standard));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'Standard'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'Standard': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_standard_options_table,
					adsl_cfg->Standard));
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, Trellis));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'Trellis'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'Trellis': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_trellis_options_table,
					adsl_cfg->Trellis));
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, TxPowerAtten));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'TxPowerAtten'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'TxPowerAtten': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%u\n", adsl_cfg->TxPowerAtten);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, CodingGain));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'CodingGain'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'CodingGain': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_coding_gain_options_table,
					adsl_cfg->CodingGain));
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, MaxBitsPerBin));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'MaxBitsPerBin'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'MaxBitsPerBin': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%X\n", adsl_cfg->MaxBitsPerBin);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, TxStartBin));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'TxStartBin'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'TxStartBin': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%X\n", adsl_cfg->TxStartBin);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, TxEndBin));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'TxEndBin'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'TxEndBin': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%X\n", adsl_cfg->TxEndBin);
  adsl_global_cfg += tmp_buff;	
  
 ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, RxStartBin));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'RxStartBin'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'RxStartBin': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%X\n", adsl_cfg->RxStartBin);
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, RxEndBin));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'RxEndBin'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'RxEndBin': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%X\n", adsl_cfg->RxEndBin);
  adsl_global_cfg += tmp_buff;  
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, RxBinAdjust));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'RxBinAdjust'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'RxBinAdjust': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_bin_adjust_options_table,
					adsl_cfg->RxBinAdjust));
  adsl_global_cfg += tmp_buff; 
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, FramingStruct));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'FramingStruct'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'FramingStruct': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_framing_struct_options_table,
					adsl_cfg->FramingStruct));
  adsl_global_cfg += tmp_buff; 
  
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, ExpandedExchange));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'ExpandedExchange'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'ExpandedExchange': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_exchange_options_table,
					adsl_cfg->ExpandedExchange));
  adsl_global_cfg += tmp_buff; 

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, ClockType));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'ClockType'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'ClockType': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%s\n", 
      get_keyword_from_look_up_t_table( adsl_clock_type_options_table,
					adsl_cfg->ClockType));
  adsl_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(adsl_conftab, offsetof(wan_adsl_conf_t, MaxDownRate));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'MaxDownRate'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'MaxDownRate': %s\n", keyword));
  adsl_global_cfg += keyword;
  adsl_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%u\n", adsl_cfg->MaxDownRate);
  adsl_global_cfg += tmp_buff;
  
  ///////////////////////////////////////////////////////////////////////////////////////
  adsl_global_cfg += "\n";
  tmp_string += adsl_global_cfg;

  return YES;
}

/*
CARD_TYPE 	= S51X
S514CPU 	= A
AUTO_PCISLOT	= NO
PCISLOT 	= 5
PCIBUS		= 0
*/
int conf_file_writer::form_wanpipe_card_location_str(string& wp_card_location_string)
{
  char tmp_buff[MAX_PATH_LENGTH];
  link_def_t * link_def;
  wandev_conf_t *linkconf;

  link_def = cfr->link_defs;
  linkconf = cfr->link_defs->linkconf;

  Debug(DBG_CONF_FILE_WRITER, ("form_wanpipe_card_location_str()\n"));

  snprintf(tmp_buff, MAX_PATH_LENGTH, "CARD_TYPE 	= %s\n",
    get_keyword_from_look_up_t_table( card_type_table, cfr->link_defs->linkconf->card_type));
  wp_card_location_string = tmp_buff;

  switch(cfr->link_defs->linkconf->card_type)
  {
  case WANOPT_S50X:

    snprintf(tmp_buff, MAX_PATH_LENGTH, "CommPort \t= %s\n",
      get_keyword_from_look_up_t_table( commport_type_options_table,
					linkconf->comm_port));
    wp_card_location_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "IOPORT\t\t= 0x%X\n", linkconf->ioport);
    wp_card_location_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "IRQ\t\t= %d\n", linkconf->irq);
    wp_card_location_string += tmp_buff;

    if(linkconf->maddr != 0){
      snprintf(tmp_buff, MAX_PATH_LENGTH, "MemAddr \t= 0x%lX\n", linkconf->maddr);
      wp_card_location_string += tmp_buff;
    }else{
      //it is auto - do not write anything
    }
    break;

  case WANOPT_S51X:
  case WANOPT_AFT:
  case WANOPT_ADSL:

    if(cfr->link_defs->linkconf->card_type != WANOPT_ADSL){
      snprintf(tmp_buff, MAX_PATH_LENGTH, "S514CPU \t= %s\n",
	cfr->link_defs->linkconf->S514_CPU_no);
      wp_card_location_string += tmp_buff;

      snprintf(tmp_buff, MAX_PATH_LENGTH, "CommPort \t= %s\n",
	get_keyword_from_look_up_t_table( commport_type_options_table,
					  cfr->link_defs->linkconf->comm_port));
      wp_card_location_string += tmp_buff;
    }


    snprintf(tmp_buff, MAX_PATH_LENGTH, "AUTO_PCISLOT \t= %s\n",
      get_keyword_from_look_up_t_table( yes_no_options_table,
        cfr->link_defs->linkconf->auto_pci_cfg));
    wp_card_location_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "PCISLOT \t= %d\n",
      cfr->link_defs->linkconf->PCI_slot_no);
    wp_card_location_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "PCIBUS  \t= %d\n",
      cfr->link_defs->linkconf->pci_bus_no);
    wp_card_location_string += tmp_buff;
    break;

  default:
    ERR_DBG_OUT(("Unsupported 'card_type' (%d) passed for saving to file!!\n",
      cfr->link_defs->linkconf->card_type));
    return NO;
  }
  
  return YES;
}

/*
//if t1/e1 card
MEDIA 		= T1
LCODE 		= B8ZS
FRAME 		= ESF
TE_CLOCK 	= NORMAL
ACTIVE_CH	= ALL
LBO 		= 0DB
*/
//write stuff needed for cards with an integrated dsu/csu
int conf_file_writer::form_fe_card_cfg_str(string& te1_cfg_string)
{
  char tmp_buff[MAX_PATH_LENGTH];
  sdla_fe_cfg_t*  fe_cfg = &cfr->link_defs->linkconf->fe_cfg;
  sdla_te_cfg_t*  te_cfg = &fe_cfg->cfg.te_cfg;
  sdla_te3_cfg_t* te3_cfg= &fe_cfg->cfg.te3_cfg;
    
  Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str()\n"));

  switch(fe_cfg->media)
  {
  case WAN_MEDIA_T1:
  case WAN_MEDIA_E1:
  case WAN_MEDIA_DS3:
  case WAN_MEDIA_E3:
  case WAN_MEDIA_56K:
    snprintf(tmp_buff, MAX_PATH_LENGTH, "FE_MEDIA	= %s\n",
      get_keyword_from_look_up_t_table( physical_medium_options_table,
        fe_cfg->media));
    te1_cfg_string = tmp_buff;
    break; 
  }
  
  ///////////////////////////////////////////////////////////////////////////////// 
  switch(fe_cfg->media)
  {
  case WAN_MEDIA_T1:
  case WAN_MEDIA_E1:
  case WAN_MEDIA_DS3:
  case WAN_MEDIA_E3:
    snprintf(tmp_buff, MAX_PATH_LENGTH, "FE_LCODE	= %s\n",
      get_keyword_from_look_up_t_table( te1_line_code_options_table,
        fe_cfg->lcode));
    te1_cfg_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "FE_FRAME	= %s\n",
    get_keyword_from_look_up_t_table( te1_frame_options_table,
      fe_cfg->frame));
    te1_cfg_string += tmp_buff;
    break; 
  }
  
  ///////////////////////////////////////////////////////////////////////////////// 
  switch(fe_cfg->media)
  {
  case WAN_MEDIA_T1:
  case WAN_MEDIA_E1:
    snprintf(tmp_buff, MAX_PATH_LENGTH, "FE_LINE		= %d\n", fe_cfg->line_no);
    te1_cfg_string += tmp_buff;
	  
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE_CLOCK 	= %s\n",
      get_keyword_from_look_up_t_table( te1_clock_mode_options_table,
        te_cfg->te_clock));
    te1_cfg_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "ACTIVE_CH	= %s\n",
      cfr->link_defs->active_channels_string);
    te1_cfg_string += tmp_buff;

    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE_HIGHIMPEDANCE	= %s\n",
      (te_cfg->high_impedance_mode == WANOPT_YES ? "YES" : "NO"));
    te1_cfg_string += tmp_buff;
    break; 
  }

  ///////////////////////////////////////////////////////////////////////////////// 
  switch(fe_cfg->media)
  {
  case WAN_MEDIA_T1:
    snprintf(tmp_buff, MAX_PATH_LENGTH, "LBO 		= %s\n",
      get_keyword_from_look_up_t_table( t1_line_build_out_options_table,
        te_cfg->lbo));
    te1_cfg_string += tmp_buff;
    break;

  case WAN_MEDIA_DS3:
  case WAN_MEDIA_E3:
/*
TE3_FRACTIONAL	= NO		# NO/YES+RDEVICE
TE3_RDEVICE	= KENTROX	# ADTRAN/DIGITAL-LINK/KENTROX/LARSCOM/VERILINK
TE3_FCS		= 16		# 16(default) / 32
TE3_RXEQ	= NO		# NO (default) / YES	
TE3_TAOS	= NO		# NO (default) / YES	
TE3_LBMODE	= NO		# NO (default) / YES	
TE3_TXLBO	= NO		# NO (default) / YES	
*/

    Debug(DBG_CONF_FILE_WRITER, ("te3_cfg->clock:  %d\n", te3_cfg->clock));

    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_CLOCK	= %s\n",
      get_keyword_from_look_up_t_table( te1_clock_mode_options_table,
        te3_cfg->clock));
    te1_cfg_string += tmp_buff;
    
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_FRACTIONAL	= %s\n",
      get_keyword_from_look_up_t_table( yes_no_options_table,
        te3_cfg->fractional));
    te1_cfg_string += tmp_buff;

    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  1\n"));
     
    if(te3_cfg->fractional == WANOPT_YES){

      Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  2\n"));

      snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_RDEVICE	= %s\n",
	get_keyword_from_look_up_t_table( te3_rdevice_type_options_table,
	  te3_cfg->rdevice_type));

      te1_cfg_string += tmp_buff;  
    } 

    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  3\n"));
    
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_FCS	= %d\n",
      te3_cfg->fcs);
    te1_cfg_string += tmp_buff;  

    ///////////////////////////////////////////////////////////////

    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  te3_cfg->liu_cfg.rx_equal: %d\n",
	te3_cfg->liu_cfg.rx_equal));
    
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_RXEQ	= %s\n",
      get_keyword_from_look_up_t_table( yes_no_options_table,
        te3_cfg->liu_cfg.rx_equal));
    te1_cfg_string += tmp_buff; 
    
    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  4\n"));

    ///////////////////////////////////////////////////////////////
 
    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str(): te3_cfg->liu_cfg.taos: %d\n",
	te3_cfg->liu_cfg.taos));
    
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_TAOS	= %s\n",
      get_keyword_from_look_up_t_table( yes_no_options_table,
        te3_cfg->liu_cfg.taos));
    te1_cfg_string += tmp_buff;  

    ///////////////////////////////////////////////////////////////
    
    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  te3_cfg->liu_cfg.lb_mode: %d\n",
	te3_cfg->liu_cfg.lb_mode));
    
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_LBMODE	= %s\n",
      get_keyword_from_look_up_t_table( yes_no_options_table,
        te3_cfg->liu_cfg.lb_mode));
    te1_cfg_string += tmp_buff; 
    
    ///////////////////////////////////////////////////////////////
    
    Debug(DBG_CONF_FILE_WRITER, ("form_fe_card_cfg_str():  te3_cfg->liu_cfg.tx_lbo: %d\n",
	te3_cfg->liu_cfg.rx_equal));
    
    snprintf(tmp_buff, MAX_PATH_LENGTH, "TE3_TXLBO	= %s\n",
      get_keyword_from_look_up_t_table( yes_no_options_table,
        te3_cfg->liu_cfg.tx_lbo));
    te1_cfg_string += tmp_buff;     
    break;
  }
  
  return YES;
}

/*
Firmware	= /etc/wanpipe/firmware/fr514.sfm
Interface 	= V35
Clocking 	= Internal
BaudRate 	= 1540000
MTU 		= 1500
UDPPORT 	= 9000
TTL 		= 255
IGNORE_FRONT_END  = NO
*/
int conf_file_writer::form_wanpipe_card_miscellaneous_options_str(string& misc_opt_string)
{
  char tmp_buff[MAX_PATH_LENGTH];
  
  Debug(DBG_CONF_FILE_WRITER, ("form_wanpipe_card_miscellaneous_options_str()\n"));
  
  switch(cfr->link_defs->linkconf->card_type)
  {
  case WANOPT_S51X:
  case WANOPT_S50X:
    snprintf(tmp_buff, MAX_PATH_LENGTH, "Firmware	= %s\n",
				cfr->link_defs->firmware_file_path);
    misc_opt_string = tmp_buff;
    break;

  case WANOPT_AFT:
  case WANOPT_ADSL:
     //no firmware file needed
     break;
  }
 
  if(cfr->link_defs->linkconf->card_type != WANOPT_ADSL){

    misc_opt_string += form_keyword_and_value_str(  common_conftab,
                                                    offsetof(wandev_conf_t, interface),
	                                            serial_interface_type_options_table,
	                                            cfr->link_defs->linkconf->interface);

    misc_opt_string += form_keyword_and_value_str(  common_conftab,
                                                    offsetof(wandev_conf_t, clocking),
                                                    serial_clock_type_options_table,
	                                            cfr->link_defs->linkconf->clocking);

    snprintf(tmp_buff, MAX_PATH_LENGTH, "BaudRate 	= %d\n",
        cfr->link_defs->linkconf->bps);
    misc_opt_string += tmp_buff;
  }
  
  snprintf(tmp_buff, MAX_PATH_LENGTH, "MTU 		= %d\n",
      cfr->link_defs->linkconf->mtu);
  misc_opt_string += tmp_buff;

  snprintf(tmp_buff, MAX_PATH_LENGTH, "UDPPORT 	= %d\n",
      cfr->link_defs->linkconf->udp_port);
  misc_opt_string += tmp_buff;

  snprintf(tmp_buff, MAX_PATH_LENGTH, "TTL\t	= %d\n",
      cfr->link_defs->linkconf->ttl);
  misc_opt_string += tmp_buff;

  snprintf(tmp_buff, MAX_PATH_LENGTH, "IGNORE_FRONT_END = %s\n",
    get_keyword_from_look_up_t_table( yes_no_options_table,
                                      cfr->link_defs->linkconf->ignore_front_end_status));
  misc_opt_string += tmp_buff;

  return YES;
}

char* conf_file_writer::form_keyword_and_value_str( key_word_t* conftab,
                                                    int offset_in_structure,
                                                    look_up_t* table,
                                                    int search_value)
{
  string tmp_str;

  tmp_str = "";

  tmp_str += get_keyword_from_key_word_t_table( conftab, offset_in_structure);
  tmp_str += "\t= ";

  tmp_str +=  get_keyword_from_look_up_t_table( table, search_value);
  tmp_str += "\n";

  return (char*)tmp_str.c_str();
}

/*
//For Frame Relay - global cfg
NUMBER_OF_DLCI 	= 2
Station 	= CPE
Signalling 	= ANSI
T391 		= 10
T392 		= 16
N391 		= 6
N392 		= 3
N393 		= 4
FR_ISSUE_FS	= YES
*/
int conf_file_writer::form_frame_relay_global_configuration_string( wan_fr_conf_t* fr_cfg,
                                                                    string& fr_global_cfg)
{
  char* keyword;
  char tmp_buff[MAX_PATH_LENGTH];

  Debug(DBG_CONF_FILE_WRITER, ("form_frame_relay_global_configuration_string()\n"));

  fr_global_cfg = "";

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, dlci_num));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'dlci_num'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'dlci_num': %s\n", keyword));
  fr_global_cfg += keyword;
  fr_global_cfg += "\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", fr_cfg->dlci_num); //cfr->obj_list->get_size());
  fr_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_look_up_t_table( frame_relay_station_type_options_table,
    fr_cfg->station);

  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find value keyword for 'station' (0x%x)!\n",fr_cfg->station));
    return NO;
  }
  snprintf(tmp_buff, MAX_PATH_LENGTH, "Station\t\t= %s\n", keyword);
  fr_global_cfg += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, signalling));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'signalling'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'signalling': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t= ";

  keyword = get_keyword_from_look_up_t_table( frame_relay_in_channel_signalling_options_table,
    fr_cfg->signalling);
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find value keyword for 'fr_cfg->signalling' (0x%x)!\n",
      fr_cfg->signalling));
    return NO;
  }
  fr_global_cfg += keyword;
  fr_global_cfg += "\n";
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, t391));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'T391'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'T391': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", fr_cfg->t391);
  fr_global_cfg += tmp_buff;
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, t392));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'T392'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'T392': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", fr_cfg->t392);
  fr_global_cfg += tmp_buff;
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, n391));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'N391'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'N391': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", fr_cfg->n391);
  fr_global_cfg += tmp_buff;
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, n392));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'N392'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'N392': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", fr_cfg->n392);
  fr_global_cfg += tmp_buff;
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, n393));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'N393'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'N393': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t\t= ";

  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", fr_cfg->n393);
  fr_global_cfg += tmp_buff;
  ///////////////////////////////////////////////////////////////////////////////////////
  keyword = get_keyword_from_key_word_t_table(fr_conftab,
                                              offsetof(wan_fr_conf_t, issue_fs_on_startup));
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find keyword for 'issue_fs_on_startup'!\n"));
    return NO;
  }
  Debug(DBG_CONF_FILE_WRITER, ("keyword for 'issue_fs_on_startup': %s\n", keyword));

  fr_global_cfg += keyword;
  fr_global_cfg += "\t= ";

  keyword = get_keyword_from_look_up_t_table( yes_no_options_table,
                                              fr_cfg->issue_fs_on_startup);
  if(keyword == NULL){
    ERR_DBG_OUT(("failed to find value keyword for 'fr_cfg->issue_fs_on_startup' (0x%x)!\n",
      fr_cfg->issue_fs_on_startup));
    return NO;
  }
  fr_global_cfg += keyword;
  fr_global_cfg += "\n";
  ///////////////////////////////////////////////////////////////////////////////////////

  return YES;
}

int conf_file_writer::form_ppp_global_configuration_string(wan_ppp_conf_t* ppp_cfg,
                                                           string& global_protocol_cfg)
{
  global_protocol_cfg = "";

  global_protocol_cfg += form_keyword_and_value_str(  ppp_conftab,
                                                      offsetof(wan_ppp_conf_t, ip_mode),
                                                      ppp_ip_mode_options_table,
                                                      ppp_cfg->ip_mode);
  return YES;
}

/*
Receive_Only	= NO
Connection	= Permanent
LineCoding	= NRZ
LineIdle	= Flag
*/
int conf_file_writer::form_chdlc_global_configuration_string( wan_chdlc_conf_t* chdlc_cfg,
                                                              string& global_protocol_cfg)
{
  global_protocol_cfg = "";

  global_protocol_cfg += form_keyword_and_value_str(  common_conftab,
                                                      offsetof(wandev_conf_t, receive_only),
                                                      yes_no_options_table,
                                                      cfr->link_defs->linkconf->receive_only);

  global_protocol_cfg += form_keyword_and_value_str(  common_conftab,
                                                      offsetof(wandev_conf_t, connection),
                                                      connection_type_options_table,
                                                      cfr->link_defs->linkconf->connection);

  global_protocol_cfg += form_keyword_and_value_str(  common_conftab,
                                                      offsetof(wandev_conf_t, line_coding),
                                                      data_encoding_options_table,
                                                      cfr->link_defs->linkconf->line_coding);

  global_protocol_cfg += form_keyword_and_value_str(  common_conftab,
                                                      offsetof(wandev_conf_t, line_idle),
                                                      line_idle_options_table,
                                                      cfr->link_defs->linkconf->line_idle);

  return YES;
}

int conf_file_writer::form_aft_global_configuration_string( wan_xilinx_conf_t* xilinx_conf,
                                                            string& global_protocol_cfg)
{
  //nothing has to be done.

  global_protocol_cfg = "";

  return YES;
}

int conf_file_writer::form_per_interface_str( string& wp_interface,
                                              list_element_chan_def* list_el_chan_def,
                                              int lip_layer_flag)
{
  char tmp_buff[MAX_PATH_LENGTH];
  string tmp_str;
  
  link_def_t* link_def = cfr->link_defs;
  //linkconf = cfr->link_defs->linkconf;

  Debug(DBG_CONF_FILE_WRITER, ("form_per_interface_str()\n"));

  chan_def_t* chandef = &list_el_chan_def->data;
  wanif_conf_t* chanconf = chandef->chanconf;
  int config_id = chanconf->config_id;
#if DBG_FLAG
  int protocol = chanconf->protocol;
#endif

  snprintf(tmp_buff, MAX_PATH_LENGTH, "\n[%s]\n", chandef->name);
  /*
  //this is very useful for debugging:
  snprintf(tmp_buff, MAX_PATH_LENGTH, "\n[%s]:config_id: %d, protocol: %d, usedby: %s\n",
		 chandef->name,
		 config_id,
		 protocol,
		 get_used_by_string(chandef->usedby));
  */
  wp_interface += tmp_buff;
  
  Debug(DBG_CONF_FILE_WRITER, ("config_id: %d (%s)\n", config_id,
    get_protocol_string(config_id)));
  
  Debug(DBG_CONF_FILE_WRITER, ("protocol: %d (%s)\n", protocol,
    get_protocol_string(protocol)));
  
  int interface_type;
  if(chandef->usedby == STACK){
    interface_type = WANCONFIG_AFT;
  }else{
/*
    if(config_id == WANCONFIG_HDLC){

      interface_type = WANCONFIG_AFT;
    }else{
*/	    
      interface_type = config_id;
//    }
  }

  if(interface_type == PROTOCOL_TDM_VOICE){
    interface_type = WANCONFIG_HDLC;
  }
  
  Debug(DBG_CONF_FILE_WRITER, ("interface_type: %d\n", interface_type));
  
  switch(interface_type)//(config_id)
  {
  case WANCONFIG_MFR:
    if(form_frame_relay_per_interface_str(tmp_str, list_el_chan_def) == NO){
      return NO;
    }
    break;

  case WANCONFIG_TTY:
  case WANCONFIG_EDUKIT:
    //empty interface string, so don't do anything
    break;
    
  case WANCONFIG_MPPP:
    /* moved to profile section. do nothing here.
    if(form_ppp_per_interface_str(tmp_str, list_el_chan_def) == NO){
      return NO;
    }
    */
    break;

  case WANCONFIG_MPCHDLC:
    if(form_chdlc_per_interface_str(tmp_str, list_el_chan_def) == NO){
      return NO;
    }
    break;

  case WANCONFIG_ADSL:
    //depending on Encapsulation ( and sub_config_id )
    if(link_def->sub_config_id == WANCONFIG_MPPP){
      if(form_ppp_per_interface_str(tmp_str, list_el_chan_def) == NO){
       return NO;
      }
    }
    
    if(form_hardware_interface_str(tmp_str, list_el_chan_def) == NO){
      return NO;
    }
    break;

  case WANCONFIG_HDLC:
    //used for runnin HDLC Streamin on S-cards
  case WANCONFIG_AFT:
    if(form_hardware_interface_str(tmp_str, list_el_chan_def) == NO){
      return NO;
    }
    break;

  default:
    ERR_DBG_OUT(("Unsupported 'interface_type' (%d) passed for saving to file!!\n",
      interface_type));
    return NO;
  }

  wp_interface += tmp_str;

  if(chandef->usedby != STACK){
    form_common_per_interface_str( wp_interface, list_el_chan_def);
  }
  
  return YES;
}

/*
[wp2fr16]
CIR 		= 64
BC  		= 64
BE  		= 0
INARP 		= YES
INARPINTERVAL 	= 10
INARP_RX	= YES
*/
int conf_file_writer::form_frame_relay_per_interface_str( string& wp_interface,
                                                          list_element_chan_def* list_el_chan_def)
{
  char tmp_buff[MAX_PATH_LENGTH];

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, cir));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->cir);
  wp_interface += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, bc));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->bc);
  wp_interface += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, be));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->be);
  wp_interface += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, inarp),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->inarp);

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, inarp_interval));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->inarp_interval);
  wp_interface += tmp_buff;

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, inarp_rx),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->inarp_rx);
  return YES;
}

/*
MULTICAST 	= NO
IPX		= YES
NETWORK		= 0xABCDEFAB
TRUE_ENCODING_TYPE	= NO
DYN_INTR_CFG	= NO
*/
int conf_file_writer::form_common_per_interface_str( string& wp_interface,
                                                     list_element_chan_def* list_el_chan_def)
{
  char tmp_buff[MAX_PATH_LENGTH];
  wandev_conf_t *linkconf = cfr->link_defs->linkconf;

  ///////////////////////////////////////////////////////////////////////////////////////
  //multicast
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, mc),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->mc);

  ///////////////////////////////////////////////////////////////////////////////////////
  //ipx
  if( linkconf->config_id == WANCONFIG_MFR ||
      linkconf->config_id == WANCONFIG_MPPP){

    wp_interface += form_keyword_and_value_str( chan_conftab,
                                                offsetof(wanif_conf_t, enable_IPX),
                                                yes_no_options_table,
                                                list_el_chan_def->data.chanconf->enable_IPX);

    ///////////////////////////////////////////////////////////////////////////////////////
    if(list_el_chan_def->data.chanconf->enable_IPX == WANOPT_YES){
      wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                        offsetof(wanif_conf_t, network_number));
      wp_interface += "\t= ";
      snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%08lX\n", list_el_chan_def->data.chanconf->network_number);
      wp_interface += tmp_buff;
    }
  }

  ///////////////////////////////////////////////////////////////////////////////////////
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, true_if_encoding),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->true_if_encoding);

  ///////////////////////////////////////////////////////////////////////////////////////
  //DYN_INTR_CFG
  switch(list_el_chan_def->data.usedby)
  {
  case WANPIPE:
  case BRIDGE_NODE:
    wp_interface += form_keyword_and_value_str( chan_conftab,
                                                offsetof(wanif_conf_t, if_down),
                                                yes_no_options_table,
                                                list_el_chan_def->data.chanconf->if_down);
    break;
  }
  return YES;
}

/*
PAP       	= YES
CHAP		= NO
USERID 	= my_userid
PASSWD   	= my_password
SYSNAME  	= my_system_name
*/
int conf_file_writer::form_ppp_per_interface_str( string& wp_interface,
                                                  list_element_chan_def* list_el_chan_def)
{
  char authenticate = NO;

  if(list_el_chan_def->data.chanconf->pap == WANOPT_YES){

    authenticate = YES;
  }
  if(list_el_chan_def->data.chanconf->chap == WANOPT_YES){
    authenticate = YES;
  }

  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, pap),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->pap);

  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, chap),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->chap);


  if(authenticate == YES){

    wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                      offsetof(wanif_conf_t, userid));
    wp_interface += "\t= ";
    wp_interface += (char*)list_el_chan_def->data.chanconf->userid;
    wp_interface += "\n";

    wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                      offsetof(wanif_conf_t, passwd));
    wp_interface += "\t= ";
    wp_interface += (char*)list_el_chan_def->data.chanconf->passwd;
    wp_interface += "\n";

    wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                      offsetof(wanif_conf_t, sysname));
    wp_interface += "\t= ";
    wp_interface += (char*)list_el_chan_def->data.chanconf->sysname;
    wp_interface += "\n";
  }

  return YES;
}

/*
IGNORE_DCD		= NO
IGNORE_CTS		= NO
IGNORE_KEEPALIVE	= NO
HDLC_STREAMING		= NO
KEEPALIVE_TX_TIMER	= 10000
KEEPALIVE_RX_TIMER	= 11000
KEEPALIVE_ERR_MARGIN	= 5
SLARP_TIMER		= 0
===========================
IGNORE_DCD		= NO
IGNORE_CTS		= NO
IGNORE_KEEPALIVE	= NO
HDLC_STREAMING		= NO
KEEPALIVE_TX_TIMER	= 10000
KEEPALIVE_RX_TIMER	= 11000
KEEPALIVE_ERR_MARGIN	= 5
SLARP_TIMER		= 1000
*/
int conf_file_writer::form_chdlc_per_interface_str(string& wp_interface,
                                                  list_element_chan_def* list_el_chan_def)
{
  char tmp_buff[MAX_PATH_LENGTH];
/*
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, ignore_dcd),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->ignore_dcd);

  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, ignore_cts),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->ignore_cts);
*/
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, ignore_keepalive),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->ignore_keepalive);
/*
  ////////////////////////////////////////////////////////////////////////////////////////////////
  //must be always NO
  wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, hdlc_streaming),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->hdlc_streaming);
*/
  ////////////////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, keepalive_tx_tmr));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->keepalive_tx_tmr);
  wp_interface += tmp_buff;

  ////////////////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, keepalive_rx_tmr));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->keepalive_rx_tmr);
  wp_interface += tmp_buff;

  ////////////////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, keepalive_err_margin));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->keepalive_err_margin);
  wp_interface += tmp_buff;

  ////////////////////////////////////////////////////////////////////////////////////////////////
  wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, slarp_timer));
  wp_interface += "\t= ";
  snprintf(tmp_buff, MAX_PATH_LENGTH, "%d\n", list_el_chan_def->data.chanconf->slarp_timer);
  wp_interface += tmp_buff;

  return YES;
}

/*
ACTIVE_CH	= 1-4
PROTOCOL	= MP_FR
IGNORE_DCD	= NO
IGNORE_CTS	= NO
HDLC_STREAMING	= YES
*/
int conf_file_writer::form_hardware_interface_str(string& wp_interface,
                                                 list_element_chan_def* list_el_chan_def)
{
  //conf_file_reader* tmp_cfr = (conf_file_reader*)list_el_chan_def->conf_file_reader;
  char tmp_buff[MAX_PATH_LENGTH];
  chan_def_t* chandef = &list_el_chan_def->data;
 
  wp_interface += "PROTOCOL",
  wp_interface += "\t= ";
  wp_interface += get_keyword_from_look_up_t_table( protocol_options_table,
                                                    list_el_chan_def->data.chanconf->protocol);
  wp_interface += "\n";

  if(cfr->link_defs->linkconf->card_type != WANOPT_ADSL){
    wp_interface += form_keyword_and_value_str( chan_conftab,
                                              offsetof(wanif_conf_t, hdlc_streaming),
                                              yes_no_options_table,
                                              list_el_chan_def->data.chanconf->hdlc_streaming);
  }

  if(cfr->link_defs->linkconf->card_type != WANOPT_S51X &&
     cfr->link_defs->linkconf->card_type != WANOPT_S50X &&
     cfr->link_defs->linkconf->card_type != WANOPT_ADSL ){

    wp_interface += get_keyword_from_key_word_t_table(chan_conftab,
                                                    offsetof(wanif_conf_t, active_ch));
    wp_interface += "\t= ";
    wp_interface += list_el_chan_def->data.active_channels_string;
    wp_interface += "\n";

    wp_interface += "IDLE_FLAG",
    wp_interface += "\t= ";
    snprintf(tmp_buff, MAX_PATH_LENGTH, "0x%02X", list_el_chan_def->data.chanconf->u.aft.idle_flag);
    wp_interface += tmp_buff;
    wp_interface += "\n";

    wp_interface += "MTU";
    wp_interface += "\t\t= ";
    snprintf(tmp_buff, MAX_PATH_LENGTH, "%u", list_el_chan_def->data.chanconf->u.aft.mtu);
    wp_interface += tmp_buff;
    wp_interface += "\n";

    wp_interface += "MRU";
    wp_interface += "\t\t= ";
    snprintf(tmp_buff, MAX_PATH_LENGTH, "%u", list_el_chan_def->data.chanconf->u.aft.mru);
    wp_interface += tmp_buff;
    wp_interface += "\n";

    if(chandef->usedby == TDM_VOICE){
      wp_interface += "TDMV_SPAN";
      wp_interface += "\t= ";
      snprintf(tmp_buff, MAX_PATH_LENGTH, "%u", chandef->chanconf->spanno);
      wp_interface += tmp_buff;
      wp_interface += "\n";

      wp_interface += "TDMV_ECHO_OFF";
      wp_interface += "\t= ";
      snprintf(tmp_buff, MAX_PATH_LENGTH, "%s",
        get_keyword_from_look_up_t_table( yes_no_options_table,
		     			  chandef->chanconf->tdmv_echo_off));
      wp_interface += tmp_buff;
      wp_interface += "\n";
    }
    
  }else{
    /*
    //HDLC streamin runs on CHDLC firmware
    if(form_chdlc_per_interface_str(wp_interface, list_el_chan_def) == NO){
      return NO;
    }
    */
  }
 
  return YES;
}

char* conf_file_writer::get_aft_lip_layer_protocol(int protocol)
{
  Debug(DBG_CONF_FILE_WRITER, ("get_aft_lip_layer_protocol(): protocol: %d\n",
    protocol));

  switch(protocol)
  {
  case WANCONFIG_MFR:
    return "fr";
        
  case WANCONFIG_TTY:
    return "tty";
    
  case WANCONFIG_MPPP:
    return "ppp";

  case WANCONFIG_MPCHDLC:
    return "chdlc";

  default:
    return "unknown";
  }
}

int conf_file_writer::form_profile_str(string& profile_str,
                                       list_element_chan_def* parent_list_el_chan_def)
{
  char tmp_buff[MAX_PATH_LENGTH];
  string global_protocol_cfg = "";
  string tmp_str = "";
  
  wan_fr_conf_t* fr_cfg;
  wan_ppp_conf_t* ppp_cfg;
  //wan_chdlc_conf_t* chdlc_cfg;

  Debug(DBG_CONF_FILE_WRITER, ("form_profile_str(): name: %s\n",
    parent_list_el_chan_def->data.name));

  chan_def_t* parent_chandef = &parent_list_el_chan_def->data;
  wanif_conf_t* parent_chanconf = parent_chandef->chanconf;
  int config_id = parent_chanconf->config_id;
#if DBG_FLAG
  int protocol = parent_chanconf->protocol;
#endif
  Debug(DBG_CONF_FILE_WRITER, ("config_id: %d (%s)\n", config_id,
    get_protocol_string(config_id)));
  
  Debug(DBG_CONF_FILE_WRITER, ("protocol: %d (%s)\n", protocol,
    get_protocol_string(protocol)));

  //this function should be called only for 'STACK' interfaces
  if(parent_chandef->usedby != STACK){
    ERR_DBG_OUT(("Not a 'STACK' interface - no profile needed!!\n"));
    return NO;
  }

  //for TTY the '.conf' file is inconsistent again!! because 'PROTOCOL=NONE'
  //instead of for example, 'MP_TTY'. So, config_id stays set to 'WANCONFIG_AFT' or
  //'WANCONFIG_ADSL'. And this is invalid.
  if(config_id == WANCONFIG_AFT || config_id == WANCONFIG_ADSL){
    config_id = WANCONFIG_TTY;
  }
  
  //form profile name
  //snprintf(tmp_buff, MAX_PATH_LENGTH, "\n[%s.%s%s]\n",
  snprintf(tmp_buff, MAX_PATH_LENGTH, "\n[%s.%s%d]\n",
      get_aft_lip_layer_protocol(config_id),
      get_aft_lip_layer_protocol(config_id),
      current_profile_number);
      //parent_chandef->addr);
  profile_str += tmp_buff;
  
  objects_list* obj_list = (objects_list*)parent_list_el_chan_def->next_objects_list;
  if(obj_list == NULL){
    ERR_DBG_OUT(("Invalid 'obj_list' pointer in 'STACK'!!\n"));
    return NO;
  }

  //profile is the same for all interfaces, so just use the 1-st one
  list_element_chan_def* list_el_chan_def = (list_element_chan_def*)obj_list->get_first();
  if(list_el_chan_def == NULL){
    ERR_DBG_OUT(("Invalid 'list_el_chan_def' pointer in 'STACK'!!\n"));
    return NO;
  }

  chan_def_t* chandef = &list_el_chan_def->data;
  wanif_conf_t* chanconf = chandef->chanconf;

  //the only protocol which has consistent '.conf' file is MFR.
  //all others are 'special cases'.
  switch(config_id)
  {
  case WANCONFIG_MFR:
    fr_cfg = &chanconf->u.fr;
    if(form_frame_relay_global_configuration_string( fr_cfg, global_protocol_cfg) == NO){
      return NO;
    }
    break;
      
  case WANCONFIG_TTY:
  //case WANCONFIG_AFT: //for TTY the '.conf' file is inconsistent again!! because 'PROTOCOL=NONE'
    //instead of for example, 'MP_TTY'. So config_id stays set to 'WANCONFIG_AFT'.
    // all protocol configuration is in 'interface' section
    // NO protocol global configuration -> profile is empty -> don't have to do anything here
    /*
    if(form_frame_relay_global_configuration_string( fr_cfg, global_protocol_cfg) == NO){
      return NO;
    }
    */
    break;

  case WANCONFIG_MPPP:
    ppp_cfg = &cfr->link_defs->linkconf->u.ppp;
    if(form_ppp_global_configuration_string(ppp_cfg, global_protocol_cfg) == NO){
      return NO;
    }
    if(form_ppp_per_interface_str(tmp_str, list_el_chan_def) == NO){
      return NO;
    }
    global_protocol_cfg += tmp_str;
    break;

  case WANCONFIG_MPCHDLC:
    //chdlc_cfg = &tmp_cfr->link_defs->linkconf->u.chdlc;
    // all protocol configuration is in 'interface' section
    // NO protocol global configuration -> profile is empty -> don't have to do anything here
    /*
    if(form_chdlc_global_configuration_string(chdlc_cfg, global_protocol_cfg) == NO){
      return NO;
    }
    */
    break;

  default:
    //profile_str += "unsupprted profile!";
    ERR_DBG_OUT(("Invalid config_id %d (%s) for Profile Section!\n",
	config_id, get_protocol_string(config_id)));
    return NO;
  }//switch()

  profile_str += global_protocol_cfg;

  return YES;
}

