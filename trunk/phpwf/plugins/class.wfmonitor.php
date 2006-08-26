<?php

/**
 * WFMonitor
 * 
 * @package		PHPWF
 * @author    Benedikt Sauter <sauter@sistecs.de>
 * @version   1.0
 * @since     PHP 4.x
 */
class WFMonitor {

  /**
   * Constructs the object WFMonitor
   *
   * @param   object    $app   Text to be displayed in ErrorBox
   * @access	public
   */
  function WFMonitor(&$app) {
    $this->app = &$app;
  } // end of function


  /**
   * Displays an inline text box on top of page
   *
   * @param   String    $msg   Text to be displayed in ErrorBox
   * @access	public
   */
  function Error($msg) {
    $this->ErrorBox($msg);
  } // end of function


  /**
   * Displays an inline text box on top of page
   *
   * @param   String    $msg   Text to be displayed in ErrorBox
   * @access	public
   */
  function ErrorBox($msg) {
    $box .="
      <table border=\"1\" width=\"100%\" bgcolor=\"#ffB6C1\">
	      <tr><td>phpWebFrame Error: $msg</td></tr>
      </table>";
    $this->app->Tpl->Set(PHPWFERROR, $box);
  } // end of function


  /**
   * Displays a JavaScript MessageBox
   *
   * @param   String    $text   Text to be displayed in MessageBox
   * @access	public
   */
  function MessageBox($text) {
    $text = str_replace("'", "`", $text);
    echo "<script language='javascript'>";
    echo "alert('".$text."');";
    echo "</script>";
  } // end of function

  /**
   * Outputs an array in readable form in page directly
   *
   * @param   Array   $array  Array to be put out to browser window
   * @access	public
   */
  function Trace($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";
  } // end of function

  /**
   * Outputs an sql query in a JavaScript dialog with the ability 
   * to copy and paste it to any other application for verification.
   *
   * @param   String  $query  SQL Query to be put out to dialog
   * @access	public
   */
  function Query($query) {
    $query = str_replace('"', "'", $query);
    echo "<HTML><BODY>";
    echo "<script language='JavaScript'>";
    echo "prompt(\"Database Query:\",\"".$query."\");";
    echo "</script>";
    echo "</BODY></HTML>";
  } // end of function


} // end of class

?>