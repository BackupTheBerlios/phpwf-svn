<?php

/**
 * Central config board for the engine
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class Page 
{
  var $engine;
  var $finalparsetarget;
  function Page(&$app)
  {
    $this->app = &$app;
    //$this->engine = &$engine;
  }

  /// load a themeset set
  function LoadTheme($theme)
  {
    //$this->app->Tpl->ReadTemplatesFromPath("themes/$theme/templates/");
    $this->app->Tpl->ReadTemplatesFromPath("sip/themes/$theme/templates/");
  }
 
  function FinalParseTarget($tpl)
  {
	$this->finalparsetarget=$tpl;
  }
 
  /// show complete page
  function Show()
  {
  	if($this->finalparsetarget=="")
    		return $this->app->Tpl->FinalParse('page.tpl');
	else
    		return $this->app->Tpl->FinalParse($this->finalparsetarget);
  }

  /// build navigation tree
  function CreateNavigation($menu,$module="",$action="")
  {
    if(count($menu)>0){
      foreach($menu as $key=>$value){
        //sip layout hack 
	if($value[first][2]!="")
          $this->app->Tpl->Set(FIRSTNAV,$value[first][0]);
        else
          $this->app->Tpl->Set(FIRSTNAV,' href="index.php?module='.$value[first][1].'" 
	  class="navi">'.$value[first][0].'</a>');
	
        /* 
	if($value[first][2]!="")
          $this->app->Tpl->Set(FIRSTNAV,' href="index.php?module='.$value[first][1].'&action='.$value[first][2].'"
          class="navi">'.$value[first][0].'</a>');
        else
          $this->app->Tpl->Set(FIRSTNAV,' href="index.php?module='.$value[first][1].'" 
	  class="navi">'.$value[first][0].'</a>');
	*/


        $this->app->Tpl->Parse(NAV,'firstnav.tpl');

	$actmod = $this->app->Secure->GetGET("module");
	$actaction = $this->app->Secure->GetGET("action");
	$actid = $this->app->Secure->GetGET("id");
	if(count($value[sec])>0){
          foreach($value[sec] as $seckey=>$secnav){
           /* 
            // add third layer 
            if(count($secnav[third])>0)
            {
              foreach($secnav[third] as $thirdkey => $thirdnav)
	      {
	        $this->app->Tpl->Set(TNAV,' href="index.php?module='.$thirdnav[1].'&action='.$thirdnav[2].'"
                class="subnavi">'.$thirdnav[0].'</a>');
                $this->app->Tpl->Parse(THIRDNAV,'thirdnav.tpl');
              }
            }
	    */
            if($secnav[2]!="")
              $this->app->Tpl->Set(SECNAV,' href="index.php?module='.$secnav[1].'&action='.$secnav[2].'"
              class="subnavi">'.$secnav[0].'</a>');
            else
              $this->app->Tpl->Set(SECNAV,' href="index.php?module='.$secnav[1].'" 
              class="subnavi">'.$secnav[0].'</a>');


		//if( ($actmod==$secnav[1] && $actaction==$secnav[2] )|| ($secnav[1]==$actmod."&action=".$actaction."&id=".$actid) )
		if( ($actmod==$secnav[1])|| ($secnav[1]==$actmod."&action=".$actaction."&id=".$actid) 
		|| ($actmod=='musterhutformcreate' && $secnav[1]=='musterhutform')
		|| ($actmod=='hutformcreate' && $secnav[1]=='hutform')
		|| ($actmod=='statistikformel' && $secnav[1]=='statistikauswerten')
		|| ($actmod=='statistikverwaltenformel' && $secnav[1]=='statistikverwalten')
		)
            		$this->app->Tpl->Parse(NAV,'secnavact.tpl');
		else
            		$this->app->Tpl->Parse(NAV,'secnav.tpl');

          }
        }
      }
    }
  }

}
?>
