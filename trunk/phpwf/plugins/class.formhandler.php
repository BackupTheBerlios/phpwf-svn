<?php

/**
 * Represent a HTML FormHandler field
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class FormHandlerField
{
  var $parsetarget;
  var $htmlobject;

  var $identifier;

  var $delmsg;
  var $delmsgcol;

  var $mandatoryok=true;
  var $mandatorymsg;
  var $mandatoryrule;
  var $mandatorycheck=false;
  var $mandatoryparse=MSG;

  var $htmlformat;
  var $dbformat;

  var $value;

  function FormHandlerField($identifier,$htmlobject)
  {
    $this->htmlobject = $htmlobject;
    $this->identifier = $identifier;
  }

  function ParseTarget($parsetarget)
  {
    $this->parsetarget = $parsetarget;
  }
}


class FormActionHandler
{
  var $template;
  var $table;
  var $submitname;
  var $locafterexe;
  var $parsetarget;
  var $pkname;
  var $pkvalue;

  var $formaction;
  var $values;
 
  var $HTMLList;
  var $externalErrors = false;

}


/// some functions for easy form processing ( save form to db ...)
class FormHandler
{
  
  var $FormList;
  var $defaultmandatorycss;

  function FormHandler(&$app)
  {
    $this->app=&$app;
  }

  function DefaultMandatoryCSSClass($cssclass)
  {
    $this->defaultmandatorycss=$cssclass;
  }

  function Create($formname,$table="",$pkname="id",$pkvalue="")
  {
    $this->FormList[$formname] = new FormActionHandler();
    $this->FormList[$formname]->mandatoryparse = "MSG";
    $this->FormList[$formname]->changelist;
    $this->FormList[$formname]->table=$table;
    $this->FormList[$formname]->pkname=$pkname;
    $this->FormList[$formname]->pkvalue=$pkvalue;
    $this->FormList[$formname]->getvaluesfromdb=false;

    $formaction=$this->app->Secure->GetGET("formaction");
    
    // create simple list fpr repesent the form as data structure 
    $this->FormList[$formname]->HTMLList = & new SimpleList();
  }


  function Template($formname,$template,$parsetarget="PAGE")
  {
    $this->FormList[$formname]->template=$template;
    $this->FormList[$formname]->parsetarget=$parsetarget;
  }




  function Execute($formname,$nextformaction)
  {

    // check if table exists
    //$this->app->DBUpgrade->Checker('tabellenname');

    $this->FormList[$formname]->formaction=$nextformaction;
    $formaction = $this->app->Secure->GetGET("formaction");

    // check for edit if id is online
    $pkname = $this->FormList[$formname]->pkname;
    if($this->FormList[$formname]->pkvalue=="")
      $this->FormList[$formname]->pkvalue=$this->app->Secure->GetGET($pkname);

    if($this->FormList[$formname]->pkvalue!="" && $formaction=="")
    { 
      $this->FormList[$formname]->getvaluesfromdb=true;
    }


    if($nextformaction=="delete")
      $formaction="delete";
    
    switch($formaction)
    {
      case "create":
	if($this->MandatoryCheck($formname))
	{
	  //print_r($this->GetAssocValueArray($formname));
	  $this->InsertFormToDB($formname);	
	  $this->GoToLocation($formname);
	} 
	else 
	{
	  // show mandatory msgs and given values
	  $this->MandatoryErrors($formname);
	  //$this->FillActualFields($formname);
	  $this->PrintForm($formname);
	}
      break;
      case "edit":
	if($this->MandatoryCheck($formname))
	{
	  //print_r($this->GetAssocValueArray($formname));
	  //$this->FillActualFields($formname);
	  $this->UpdateFormToDB($formname);	
	  $this->GoToLocation($formname);
	} 
	else 
	{
	  // show mandatory msgs and given values
	  $this->MandatoryErrors($formname);
	  //$this->FillActualFields($formname);
	  $this->PrintForm($formname);
	}
      break;

      case "replace":
	if($this->MandatoryCheck($formname))
	{
	  //print_r($this->GetAssocValueArray($formname));
	  if($this->FormList[$formname]->pkvalue=="")
	    $this->InsertFormToDB($formname);	
	  else
	    $this->UpdateFormToDB($formname);	
	  $this->GoToLocation($formname);
	} 
	else 
	{
	  // show mandatory msgs and given values
	  $this->MandatoryErrors($formname);
	  //$this->FillActualFields($formname);
	  $this->PrintForm($formname);
	}
      break;

      case "delete":
	// delete actual data
	$pkname=$this->FormList[$formname]->pkname;
	$pkvalue=$this->FormList[$formname]->pkvalue;
	$table=$this->FormList[$formname]->table;
	
	$pkvalue = $this->app->DB->Select("SELECT $pkname FROM `$table` 
	  WHERE userid='".$this->app->User->GetID()."' AND `$pkname`='$pkvalue' LIMIT 1");

	$this->app->DB->Delete("DELETE FROM `$table` WHERE `$pkname`='$pkvalue' LIMIT 1");

	$this->GoToLocation($formname);
      break;
      default:
	$this->PrintForm($formname);
    }
     
  }

  function GoToLocation($formname)
  {
    header("Location: http://".$_SERVER['HTTP_HOST']
                       .dirname($_SERVER['PHP_SELF'])
		       ."/".$this->FormList[$formname]->locafterexe);
  }

  function GetAssocValueArray($formname,$dbformat=true)
  {
    $htmllist = &$this->FormList[$formname]->HTMLList;

    if($htmllist->items > 0)
    {
      $field = &$htmllist->getFirst();
      for($i=0; $i <= $htmllist->items; $i++)
      {
	if(get_class($field->htmlobject)=="blindfield")
	{
	  $value = $field->htmlobject->value;	
	}
	else {
	  if($field->value=="")
	    $value = $field->htmlobject->defvalue;
	  else
	    $value = $field->value;
	}
//	  echo "hier {$field->identifier} {$field->value} jetzt $value<br>";

	// convert html to database format
	if($dbformat)
	{
	  $value = $this->app->String->Convert(
	      $value,$field->htmlformat,$field->dbformat);
	} 
	
	$ret[$field->identifier]=$value;
	$field = &$htmllist->getNext();
      }
    }
    return $ret;
  }

  function InsertFormToDB($formname)
  {
    $this->app->DB->InsertArr(
      $this->FormList[$formname]->table,
      $this->FormList[$formname]->pkname,
      $this->GetAssocValueArray($formname)
      );
  }

  function UpdateFormToDB($formname)
  {
    $this->app->DB->UpdateArr(
      $this->FormList[$formname]->table,
      $this->FormList[$formname]->pkvalue,
      $this->FormList[$formname]->pkname,
      $this->GetAssocValueArray($formname)
      );
  }
 

  /// define output (html) and input (database) format
  function HTMLToDBConvert($formname,$identifier,$html,$db)
  { 
    $htmllist = &$this->FormList[$formname]->HTMLList;

    if($htmllist->items>0)
    {
      $field = &$htmllist->getFirst();
     
      for($i=0; $i <= $htmllist->items; $i++)
      {
	if($field->identifier == $identifier)
	{
	  $field->htmlformat = $html;
	  $field->dbformat = $db;
	}
	$field = &$htmllist->getNext();
      }
    }
  }


      
  function AddMandatoryField($formname,$identifier,$msg,$rule="",$parsetarget="MSG")
  { 
    if($rule=="")
      $rule="notempty";

    $htmllist = &$this->FormList[$formname]->HTMLList;

    if($htmllist->items>0)
    {
      $form = &$htmllist->getFirst();
     
      for($i=0; $i <= $htmllist->items; $i++)
      {
	if($form->identifier == $identifier)
	{
	  $form->mandatorycheck = true;
	  $form->mandatorymsg = $msg;
	  $form->mandatoryrule = $rule;
	  if($parsetarget!="")
	    $form->mandatoryparse = $parsetarget;
	}
	$form = &$htmllist->getNext();
      }
    }
  }

  function SetExternalError($formname) {
    $this->FormList[$formname]->externalErrors = true;
    $this->app->Tpl->Set(OPENERROR,"");
    $this->app->Tpl->Set(CLOSEERROR,"");
  } // end of funtion

  function MandatoryCheck($formname)
  {
    // Assume no errors
    $dismiss = true;

    // Externally set error (manual set)
    if ($this->FormList[$formname]->externalErrors)
      $dismiss = false;

    $htmllist = &$this->FormList[$formname]->HTMLList;

    if($htmllist->items>0)
    {
      $form = &$htmllist->getFirst();
      for($i=0; $i <= $htmllist->items; $i++)
      {
	if($form->mandatorycheck)
	{
	  if(!$this->app->Secure->RuleCheck($form->value,$form->mandatoryrule))
	  {
	    $form->mandatoryok = false;
	    $dismiss=false;
            $this->app->Tpl->Set(OPENERROR,"");
	    $this->app->Tpl->Set(CLOSEERROR,"");

	  }
	}
	$form = &$htmllist->getNext();
      }
    }
    return $dismiss;
  }


  function MandatoryErrors($formname)
  {
    $htmllist = &$this->FormList[$formname]->HTMLList;

    if($htmllist->items>0)
    {
      $form = &$htmllist->getFirst();
      for($i=0; $i <= $htmllist->items; $i++)
      {
	if(!$form->mandatoryok)
	{
	  $this->app->Tpl->Add($form->mandatoryparse,$form->mandatorymsg);
	  // mark up error field
	  $form->htmlobject->class=$this->defaultmandatorycss;
	  //$form->mandatoryrule = $rule;
	}
	$form = &$htmllist->getNext();
      }
    }
  }


  function NewField($formname,$htmlobject,$identifier="",$parsetarget="")
  {
    if($identifier=="")
      $identifier = $htmlobject->name;

    if($parsetarget=="")
      $parsetarget = strtoupper($htmlobject->name);

    // create new formhandlerfield
    $field = new FormHandlerField($identifier,$htmlobject);
    $field->ParseTarget($parsetarget);


    // nur wenn werte vom formular kommen, diese wirklich als value hernehmen
    if($this->app->Secure->GetPOST($identifier)!="")
    {
      $field->value = $this->app->Secure->GetPOST($identifier);
    }
    
    /*else
    {
      //$field->value = $field->htmlobject->value;
    }
    */	

    $this->FormList[$formname]->HTMLList->Add($field);
  }


  function DeleteMsg($formname,$delmsg,$delmsgcol)
  {
    $this->FormList[$formname]->delmsg = $delmsg;
    $this->FormList[$formname]->delmsgcol = $delmsgcol;
  }

  function PrintForm($formname)  // work only with hmtlobjects
  {
    if($this->FormList[$formname]->formaction=="edit" && $this->FormList[$formname]->pkvalue=="")
    {
    }
    else
    {
      // show form 
      // go through htmllist an parse every field
      $htmllist = &$this->FormList[$formname]->HTMLList;
    
      //load values from db when action = update
      if($this->FormList[$formname]->getvaluesfromdb)
      {
	if($htmllist->items>0)
	{
	  $field = &$htmllist->getFirst();
	  
	  $pkname = $this->FormList[$formname]->pkname;
	  $pkvalue = $this->FormList[$formname]->pkvalue;
	  $table = $this->FormList[$formname]->table;
	  
	  while($field)
	  {
	    $value = $this->app->DB->Select("SELECT `{$field->identifier}` FROM 
	      `$table` WHERE `$pkname`='$pkvalue' LIMIT 1"); 
	    
	    //value im html 
	    $field->htmlobject->value = $value;
	    
	    $field = &$htmllist->getNext();
	  }
	}
	$this->FillActualFields($formname); // fuer die datenbank 
      }
      else 
	$this->FillActualFields($formname,false); // fuer die datenbank 


    
      if($htmllist->items>0)
      {
	$field = &$htmllist->getFirst();
	while($field)
	{
	  $htmlobject = &$field->htmlobject;
	  $this->app->Tpl->Add($field->parsetarget,$htmlobject->Get().$htmlobject->GetClose());
	  $field = &$htmllist->getNext();
	}
      }
      $formaction = $this->FormList[$formname]->formaction;
  
      $module = $this->app->Secure->GetGET("module","alpha");
    
      $action = $this->app->Secure->GetGET("action","alpha");
    
      $pkname = $this->FormList[$formname]->pkname;
      $pkvalue = $this->FormList[$formname]->pkvalue;
    
      $this->app->Tpl->Set(ACTION,  ".".
	$_SERVER[PHP_SELF]."?module=$module&action=$action&formaction=$formaction
	&$pkname=$pkvalue");
  
      $this->app->Tpl->Parse(
      	$this->FormList[$formname]->parsetarget,
	$this->FormList[$formname]->template
	);
    }
  }


  // add form fields to values for db input

  function FillActualFields($formname,$convert=true) // fuer die datenbank 
  {
    $htmllist = &$this->FormList[$formname]->HTMLList;
    if($htmllist->items>0)
    {
      $field = &$htmllist->getFirst();
      for($i=0; $i <= $htmllist->items; $i++)
      {
	
	if($this->app->Secure->GetPOST($field->identifier)!="")
	{
	  $field->value = $this->app->Secure->GetPOST($field->identifier);
	}else
	{
	  $field->value = $field->htmlobject->value;
	}
	
	
	if($field->value!="" && $convert){
	  $value = $this->app->String->Convert(
	    //$field->value,$field->htmlformat,$field->dbformat);
	    $field->value,$field->dbformat,$field->htmlformat);
	
	  $value = $this->app->String->decodeText($value);
	  $field->value = $value;
	} 

	if(get_class($htmlobject)=="blindfield")
	  $field->value=$field->htmlobject->value;
 	
	
	$field->htmlobject->value=$field->value;

	
	$field = &$htmllist->getNext();
      }
    }
  }

        
  function LocationAfterExecute($formname,$target)
  {
    $this->FormList[$formname]->locafterexe=$target;
  }
	     

}
?>
