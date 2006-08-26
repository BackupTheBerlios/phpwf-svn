<?php

//include ("phpwf/engine/class.engine.php");

include ("phpwf/plugins/class.formhandler.php");
include ("phpwf/plugins/class.acl.php");
include ("phpwf/plugins/class.user.php");
include ("phpwf/plugins/class.page.php");
include ("phpwf/plugins/class.phpwfapi.php");
include ("phpwf/plugins/class.templateparser.php");
include ("phpwf/plugins/class.secure.php");
include ("phpwf/plugins/class.db.php");
include ("phpwf/plugins/class.widgets.php");
include ("phpwf/plugins/class.wfmonitor.php");
include ("phpwf/plugins/class.string.php");



include("phpwf/htmltags/all.php");
include("phpwf/types/class.simplelist.php");

/**
 * Main Application class.
 * An application class with login and authentification.
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class Application
{

    var $ActionHandlerList;
    var $ActionHandlerDefault;

    function Application()
    {
      session_start();
    
      $this->Secure         = & new Secure();   // empty $_GET, and $_POST so you
                                                // have to need the secure layer always
      $this->FormHandler    = & new FormHandler($this);
      $this->User           = & new User($this);
      $this->acl            = & new Acl($this);
      $this->WF             = & new phpWFAPI($this);
      $this->WFM            = & new WFMonitor($this);
      $this->Tpl            = & new TemplateParser();
      $this->Page           = & new Page($this);
      $this->String         = & new String();
      $this->Widgets        = & new Widgets($this);
      $this->SipTable       = & new SipTable($this);
          
      require ("sip/conf/main.conf.php");
      $this->DB             = new DB($WFdbhost,$WFdbname,$WFdbuser,$WFdbpass);

      $this->WFconf=$WFconf;


      // leiste oben (ab und anmelden)
      if(is_numeric($this->User->GetID()))
      {
        $userid=$this->User->GetID();
        $name = $this->DB->Select("SELECT CONCAT(vorname,' ',nachname) FROM person WHERE id='$userid'");
        $this->Tpl->Set(USERNAME,"Benutzer: $name");
        $this->Tpl->Set(ABMELDEN,"<a href=\"index.php?module=welcome&action=logout\">Abmelden</a> |");
      }
      else
        $this->Tpl->Set(ABMELDEN,"<a href=\"index.php?module=welcome&action=login\">Anmelden</a> |");
    }


    function ActionHandlerInit(&$caller)
    {
      $this->caller=&$caller;
    }

 
    function ActionHandler($command,$function)
    {
      $this->ActionHandlerList[$command]=$function; 
    }
    
    function DefaultActionHandler($command)
    {
      $this->ActionHandlerDefault=$command;
    }

    
    function ActionHandlerListen(&$app)
    {
      $action = $app->Secure->GetGET("action","alpha");
      if($action!="")
	$fkt = $this->ActionHandlerList[$action];
      else
	$fkt = $this->ActionHandlerList[$this->ActionHandlerDefault];


      // check permissions

      $this->caller->$fkt();
    }

}
?>
