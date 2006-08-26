<?php

/**
 * Represent a system user
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class User 
{

  function User(&$app)
  {
    $this->app = &$app;
  }

  function GetID()
  { 
    return $this->app->DB->Select("SELECT person_id FROM person_online WHERE sessionid='".session_id()."'
      AND ip='".$_SERVER[REMOTE_ADDR]."' AND login='1'");
  }

  function GetType()
  { 
    $type = $this->app->DB->Select("SELECT gruppe FROM person WHERE id='".$this->GetID()."'");
    if($type=="")
      $type = $this->app->WFconf[defaultgroup];
    
    return $type;
  }

  function GetGroup()
  {
    return $this->GetType();
  }

  function GetName()
  { 
    return $this->app->DB->Select("SELECT benutzername FROM person WHERE id='".$this->GetID()."'");
  }

  /**
   * Checks for an already existing username in database
   *
   * @param   String  $username  Username to be checked for existance
   * @return  bool  TRUE if username already exists, FALSE otherwise
   * @access	public
   */
  function UsernameExists($username) { 
    $sql = "SELECT benutzername FROM person WHERE benutzername ='".$username."'";
    $count = $this->app->DB->Count($sql);

    if ($count > 0)
      return true;

    return false;
  } // end of function



  /**
   * set/define an user depending variable 
   *
   * @param  String  $name  Name/Key of Variable
   * @param  String  $value Value can be a var,array or object 
   * @return  bool   true after update or creation
   * @access	public
   */
  function SetVar($name,$value) { 

    $sql = "SELECT variables FROM person WHERE id ='".$this->app->User->GetID()."'";
    $variablesstring = $this->app->DB->Select($sql);

    if($variablesstring=="")
    {
    	$variables = array();
    }
    else 
    {
	$variables = unserialize($variablesstring);
    }

    $variables[$name]=$value;

    $variablesstring = serialize($variables);

    $sql = "UPDATE person SET variables='$variablesstring' WHERE id ='".$this->app->User->GetID()."'";
    $this->app->DB->Update($sql);

    return true;
  } // end of function


  /**
   * get an user depending variable 
   *
   * @param  String  $name  Name/Key of Variable
   * @return  void   return a var, an array or object 
   * @access	public
   */
  function GetVar($name) { 
  	$sql = "SELECT variables FROM person WHERE id ='".$this->app->User->GetID()."'";
      	$variablesstring = $this->app->DB->Select($sql);
	$variables = unserialize($variablesstring);
	return $variables[$name];

  } // end of function



  /**
   * delete an user depending variable 
   *
   * @param  String  $name  Name/Key of Variable
   * @return  bool   true after update or creation
   * @access	public
   */
  function DelVar($name) { 

    $sql = "SELECT variables FROM person WHERE id ='".$this->app->User->GetID()."'";
    $variablesstring = $this->app->DB->Select($sql);

    if($variablesstring=="")
    {
    	$variables = array();
    }
    else 
    {
	$variables = unserialize($variablesstring);
    }

    foreach($variables as $key=>$value)
    {
	if($key!=$name)
		$newvariables[$key]=$value;
    }
     $variables = $newvariables;

    $variablesstring = serialize($variables);

    $sql = "UPDATE person SET variables='$variablesstring' WHERE id ='".$this->app->User->GetID()."'";
    $this->app->DB->Update($sql);

    return true;
  } // end of function



}
?>
