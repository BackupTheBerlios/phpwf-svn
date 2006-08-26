<?php

/**
 * Widgets
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class Widgets {

  /**
   * Constructs the object
   * 
   * @param   $app    Application Main Application handler
   *
   * @access	public
   */
  function Widgets(&$app) {
    $this->app = &$app;
  } // end of function


  /**
   * SIP - db specific method to generate options for an HTML Select (DropDown). <br />
   * In case the option $addEmptyOption is set to true
   * 
   * @param   $key              String    Key to be searchd for in database table options
   * @param   $addEmptyOption   Bool      Adds the Value "Bitte wählen ..." on top of the list using "" as option value [Default: false]
   *
   * @return  Array     List containing the options array to be pushed into the HTML input select
   * @access	public
   */
  function Options($key, $addEmptyOption=false) {
    $arr = $this->app->DB->SelectArr("SELECT anzeige,wert FROM optionen WHERE typ='$key'");

    if ($addEmptyOption) {
        $arr[-1] = Array ('name' => 'Bitte wählen ...', 'id' => "");
        ksort($arr);
    } // end of if - add the Please Choose option in top of options list with a -1 value
    return $arr;

  } // end of function

  /**
   * Returns a given databasen date of form 
   * YYYY-MM-DD in human readable (german) DD.MM.YYYY
   * 
   * @param   $dbdate   String  Database date in format YYYY-MM-DD 
   *
   * @return  String  Humanly readable date in format DD.MM.YYYY
   * @access	public
   */
  function GetHRDate($dbdate) {
    $year  = substr($dbdate, 0, 4);
    $month = substr($dbdate, 5, 2);
    $day   = substr($dbdate, 8, 2);
    return "$day.$month.$year";
  }

}
?>
