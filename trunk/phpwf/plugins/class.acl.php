<?php

/**
 * Access Control
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class Acl 
{
  //var $engine;
  function Acl(&$app)
  {
    $this->app = &$app;
  }


  function CheckTimeOut()
  {
    // check if user is applied 
    $sessid =  $this->app->DB->Select("SELECT sessionid FROM person_online,person WHERE
       login='1' AND sessionid='".session_id()."' AND person.id=person_online.person_id AND person.zustand='1' LIMIT 1");
    
    if(session_id() == $sessid)
    { 
      // check if time is expired
      $time =  $this->app->DB->Select("SELECT UNIX_TIMESTAMP(time) FROM person_online,person WHERE
       login='1' AND sessionid='".session_id()."' AND person.id=person_online.person_id AND person.zustand='1' LIMIT 1");

      if((time()-$time) > $this->app->WFconf[logintimeout])
      {
	$this->app->WF->ReBuildPageFrame();
	$this->Logout("Ihre Zeit ist abgelaufen, bitte melden Sie sich erneut an.");
	return false;
      }
      else {
	// update time
	 $this->app->DB->Update("UPDATE person_online,person SET person_online.time=NOW() WHERE
            login='1' AND sessionid='".session_id()."' AND person.id=person_online.person_id AND person.zustand='1'");
	return true; 
      }
    }

  }

  function Check($usertype,$module,$action)
  {
    $ret = false;
    $permissions = $this->app->WFconf[permissions][$usertype][$module];
    
    while (list($key, $val) = @each($permissions)) 
    {
      if($val==$action)
      {
	$ret = true;
	break;
      }
    }
    
    if(!$ret)
      $this->app->Tpl->Parse(PAGE,"permissiondenied.tpl");

    return $ret;
  }

  function Login()
  {
    $username = $this->app->Secure->GetPOST("username");
    $password = $this->app->Secure->GetPOST("password");
  
    if($username=="" && $password==""){
      $this->app->Tpl->Set(LOGINMSG,"Bitte geben Sie Benutzername und Passwort ein.");  
      $this->app->Tpl->Parse(PAGE,"login.tpl");
    }
    elseif($username==""||$password==""){
      $this->app->Tpl->Set(LOGINERRORMSG,"Bitte geben Sie einen Benutzername und ein Passwort an.");  
      $this->app->Tpl->Parse(PAGE,"loginerror.tpl");
    }
    else {
      $encrypted = $this->app->DB->Select("SELECT kennwort FROM person 
        WHERE benutzername='".$username."' AND zustand='1' LIMIT 1");

      $password = substr($password, 0, 8);

      if (crypt( $password,  $encrypted ) == $encrypted )
      { 
        $person_id = $this->app->DB->Select("SELECT id FROM person 
          WHERE benutzername='".$username."' AND zustand='1' LIMIT 1");
      }
      else { $person_id = ""; }

      if(is_numeric($person_id))
      { 
        $this->app->DB->Insert("INSERT INTO person_online (person_id, sessionid, ip, login, time)
          VALUES ('".$person_id."','".session_id()."','".$_SERVER[REMOTE_ADDR]."','1',NOW())");

	// anzahl der besuche aktualisieren

	// letzter besuch aktualisieren
	$sql = "SELECT DATE_FORMAT(MAX(time),'%Y-%m-%d') FROM person_online WHERE login=0 AND person_id='$person_id' LIMIT 1";
	$last = $this->app->DB->Select($sql);
	$sql = "UPDATE person SET last_login='$last' WHERE id='$person_id'";
	$this->app->DB->Update($sql);


	// vorlezter besuch aktualisieren
	$sql = "SELECT COUNT(login) FROM person_online WHERE login=0 AND person_id='$person_id'";
	$count = $this->app->DB->Select($sql);
	$sql = "UPDATE person SET anzahl_logins='$count' WHERE id='$person_id'";
	$this->app->DB->Update($sql);



        header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
      }
      else
      { 
	$this->app->Tpl->Set(LOGINERRORMSG,"Benutzername oder Passwort falsch.");  
        $this->app->Tpl->Parse(PAGE,"loginerror.tpl");
      }

    }
  }

  function Logout($msg="")
  {
    $username = $this->app->User->GetName();
    $this->app->DB->Delete("UPDATE person_online SET login='0' WHERE person_id='".$this->app->User->GetID()."'");
    session_regenerate_id(true);
    //header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
    //$this->app->Tpl->Set(LOGINERRORMSG,$msg);  
    $this->app->Tpl->Parse(PAGE,"logintimeout.tpl");
  }


  function CreateAclDB()
  {

  }

}
?>
