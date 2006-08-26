<?php

/**
 * Represent a template (file.tpl)
 * 1. zu jedem Template muss es in einem anderen Template eine 
 * Variable geben in htmlheader.tpl PAGE fuer page.tpl
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class ThemeTemplate {
  var $NAME; //Name des Templates
  var $ORIGINAL; //Parse - Text Vorlage
  var $VARS; //assoziatives Array mit Variablennamen als Index

  function ThemeTemplate($_path, $_file){
    $fp=@fopen($_path.$_file,"r");
    if($fp){
      $contents = fread ($fp, filesize($_path.$_file));
      fclose($fp);
    }
    $this->NAME=$_file;
    $this->ORIGINAL=$contents;
    $this->CreateVarArray();
  }


  function CreateVarArray(){
    $this->SetVar("",'');
    $parsevar=false;
    $tmpvar='';
    for($i=0; $i< strlen($this->ORIGINAL); $i++){
      $sign = substr($this->ORIGINAL,$i,1);
      if($sign=="[") $parsevar=true;
      if($parsevar==true && $sign!="[" && $sign!="]") $tmpvar.= $sign;
      if($sign=="]"){
	$parsevar=false;
	if(!preg_match("/ /i", $tmpvar)) $this->SetVar($tmpvar,'');
	$tmpvar='';
      }	
    }
  }

  function AddVar($_var, $_value){ $this->VARS[$_var]=$this->VARS[$_var].$_value; }
  function SetVar($_var, $_value){ $this->VARS[$_var]=$_value; }

}

/*********************** Class PcmsTemplate ****************************/
/// Main Parser for building the html skin (gui) 
class TemplateParser { 
  var $TEMPLATELIST;
  var $VARARRAY;

  function TemplateParser(){ $this->TEMPLATELIST=''; }


  function ResetParser()
  {
    unset($this->TEMPLATELIST);
    unset($this->VARARRAY);
  }

  function ReadTemplatesFromPath($_path){
    $directory=opendir($_path);
    while ($file=readdir($directory)){
      if(strstr($file, '.tpl')){ 
	$this->TEMPLATELIST[$file] = new ThemeTemplate($_path,$file);	
      }		
      $i++;
    }
    closedir($directory);
    //$this->CreateVarArray();
  }

  function CreateVarArray(){
    foreach($this->TEMPLATELIST as $template=>$templatename){
      if(count($this->TEMPLATELIST[$template]->VARS) > 0){
        foreach($this->TEMPLATELIST[$template]->VARS as $key=>$value){
	  $this->VARARRAY[$key]=$value;
	}
      }
    }
  }

  function ShowVariables(){
    foreach($this->VARARRAY as $key=>$value)
    echo "<b>$key =></b>".htmlspecialchars($value)."<br>";
  }

  function ParseVariables($text){
    foreach($this->VARARRAY as $key=>$value)
      $text = str_replace('['.$key.']',$value,$text);
    // fill empty vars
    return $text;
  }

  function ShowTemplates(){
    foreach ($this->TEMPLATELIST as $key=> $value){
      foreach ($value as $key1=> $text){
	if(!is_array($text))echo "$key ".htmlspecialchars($text)."<br>";
	if(is_array($text))foreach($text as $key2=>$value2) echo $key2." ".$value2;
      }
      echo "<br><br>";
    }
  }

  function Set($_var,$_value){ $this->VARARRAY[$_var]=$_value; }

  function Add($_var,$_value){  $this->VARARRAY[$_var]=$this->VARARRAY[$_var].$_value; }

  function Parse($_var, $_template){
    if($_template!=""){
      //alle template variablen aufuellen mit den werten aus VARARRAY 
      if(count($this->TEMPLATELIST[$_template]->VARS)>0){ 
	foreach ($this->TEMPLATELIST[$_template]->VARS as $key=> $value){
	  $this->TEMPLATELIST[$_template]->SetVar($key,$this->VARARRAY[$key]);
      }
      
      //ORIGINAL auffuellen
      $tmptpl = $this->TEMPLATELIST[$_template]->ORIGINAL;
      foreach ($this->TEMPLATELIST[$_template]->VARS as $key=>$value){
	$tmptpl = str_replace("[".$key."]",$value, $tmptpl);	
      }
      }
      //aufgefuelltes ORIGINAL in $t_var add($_var,ORIGINAL)
      $this->Add($_var,$tmptpl);
    }
  }

  function AddAndParse($_var, $_value, $_varparse, $_templateparse){
    $this->Set($_var, $_value);
    $this->Parse($_varparse,$_templateparse);
  }

  function FinalParse($_template){
    if($_template!=""){
      //alle template variablen aufuellen mit den werten aus VARARRAY
      if(count($this->TEMPLATELIST[$_template]->VARS)>0){ 
	foreach ($this->TEMPLATELIST[$_template]->VARS as $key=> $value)
	  $this->TEMPLATELIST[$_template]->SetVar($key,$this->VARARRAY[$key]);
      }
    }
    //ORIGINAL auffuellen
    $tmptpl = $this->TEMPLATELIST[$_template]->ORIGINAL;
    
    if(count($this->TEMPLATELIST[$_template]->VARS)>0){ 
      foreach ($this->TEMPLATELIST[$_template]->VARS as $key=>$value)
	$tmptpl = str_replace("[".$key."]",$value, $tmptpl);
    }
    
    if(count($this->VARARRAY)>0)
      foreach($this->VARARRAY as $key=>$value)
	$tmptpl = str_replace('['.$key.']',$value,$tmptpl);
    
    return $tmptpl;
  }

} 
?>
