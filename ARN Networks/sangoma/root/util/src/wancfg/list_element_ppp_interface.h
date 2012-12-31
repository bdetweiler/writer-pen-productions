/***************************************************************************
                          list_element_ppp_interface.h  -  description
                             -------------------
    begin                : Thu Mar 18 2004
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

#ifndef LIST_ELEMENT_PPP_IF_H
#define LIST_ELEMENT_PPP_IF_H

#include "list_element_chan_def.h"

#define DBG_LIST_ELEMENT_PPP_INTERFACE 1

/**
  *@author David Rokhvarg
  */

class list_element_ppp_interface : public list_element_chan_def  {

  wan_sppp_if_conf_t* ppp;
  
public:

  list_element_ppp_interface()
  {
    Debug(DBG_LIST_ELEMENT_PPP_INTERFACE,
      ("list_element_ppp_interface::list_element_ppp_interface()\n"));

    //set default configuration
    data.chanconf->config_id = WANCONFIG_MPPP;

    data.chanconf->pap = 0;
    data.chanconf->chap = 0;
    //data.chanconf->userid
    //data.chanconf->passwd 
    //data.chanconf->sysname

    //the above actually should be here, but legacy
    //structures do not allow.
    //nothing to do for now
    ppp = &data.chanconf->u.ppp;
  }

  ~list_element_ppp_interface()
  {
    Debug(DBG_LIST_ELEMENT_PPP_INTERFACE,
      ("list_element_ppp_interface::~list_element_ppp_interface()\n"));
  }

};

#endif
