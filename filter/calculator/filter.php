<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Filter embedding a javascript calculator
 *
 * This filter looks for the text $$calc$$ and replaces it with a javascript calculator.
 *
 * @package    filter
 * @subpackage calculator
 * @see        ????
 * @copyright  2012 Ian Wild <ian.wild@heavy-horse.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_calculator extends moodle_text_filter {

    /**
     * @var array global configuration for this filter
     *
     * This might be eventually moved into parent class if we found it
     * useful for other filters, too.
     */
    protected static $globalconfig;

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {

        if (!isset($options['originalformat'])) {
            // if the format is not specified, we are probably called by {@see format_string()}
            // in that case, it would be dangerous to replace text with the image because it could
            // be stripped. therefore, we do nothing
            return $text;
        }
        if (in_array($options['originalformat'], explode(',', $this->get_global_config('formats')))) {
            $this->replace_calc_marker($text);
        }
        return $text;
    }

    ////////////////////////////////////////////////////////////////////////////
    // internal implementation starts here
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the global filter setting
     *
     * If the $name is provided, returns single value. Otherwise returns all
     * global settings in object. Returns null if the named setting is not
     * found.
     *
     * @param mixed $name optional config variable name, defaults to null for all
     * @return string|object|null
     */
    protected function get_global_config($name=null) {
        $this->load_global_config();
        if (is_null($name)) {
            return self::$globalconfig;

        } elseif (array_key_exists($name, self::$globalconfig)) {
            return self::$globalconfig->{$name};

        } else {
            return null;
        }
    }

    /**
     * Makes sure that the global config is loaded in $this->globalconfig
     *
     * @return void
     */
    protected function load_global_config() {
        if (is_null(self::$globalconfig)) {
            self::$globalconfig = get_config('filter_calculator');
        }
    }

    /**
     * Replace marker found in the text with a calculator
     *
     * @param string $text to modify
     * @return void
     */
    protected function replace_calc_marker(&$text) {
        global $CFG, $OUTPUT, $PAGE;
        
        $lang = current_language();
        $theme = $PAGE->theme->name;

        // might be an idea to store calculator javascript in the database
	/*	$str_calc = '<!-- Calculator Start -->
						<form name="Keypad" action>
						<div align="left">
						<blockquote>
						<table border="1" width="50" height="60" cellpadding="2" cellspacing="0" bgcolor="#9FBAD0" bordercolor="#9FBAD0">
						<tr> 
						<td colspan="3" align="middle"> 
						<input name="ReadOut" type="Text" size="24" value="0"
						          width="100%">
						</td>
						<td> 
						<input name="btnClear" type="Button" value="  C  " onClick="Clear()">
						</td>
						<td> 
						<input name="btnClearEntry" type="Button" value=" CE " onClick="ClearEntry()">
						</td>
						</tr>
						<tr> 
						<td> 
						<input name="btnSeven" type="Button" value="  7  " onClick="NumPressed(7)">
						</td>
						<td> 
						<input name="btnEight" type="Button" value="  8  " onClick="NumPressed(8)">
						</td>
						<td> 
						<input name="btnNine" type="Button" value="  9  " onClick="NumPressed(9)">
						</td>
						<td> </td>
						<td> 
						<input name="btnNeg" type="Button" value=" +/- " onClick="Neg()">
						</td>
						<td> 
						<input name="btnPercent" type="Button" value=" % " onClick="Percent()">
						</td>
						</tr>
						<tr> 
						<td> 
						<input name="btnFour" type="Button" value="  4  " onClick="NumPressed(4)">
						</td>
						<td> 
						<input name="btnFive" type="Button" value="  5  " onClick="NumPressed(5)">
						</td>
						<td> 
						<input name="btnSix" type="Button" value="  6  " onClick="NumPressed(6)">
						</td>
						<td> </td>
						<td align="middle"> 
						<p align="left"> 
						<input name="btnPlus" type="Button" value="  +  "
						          onClick="Operation(\'+\')">
						</p>
						</td>
						<td align="middle"> 
						<input name="btnMinus" type="Button" value="  -  "
						          onClick="Operation(\'-\')">
						</td>
						</tr>
						<tr> 
						<td> 
						<input name="btnOne" type="Button" value="  1  " onClick="NumPressed(1)">
						</td>
						<td> 
						<input name="btnTwo" type="Button" value="  2  " onClick="NumPressed(2)">
						</td>
						<td> 
						<input name="btnThree" type="Button" value="  3  " onClick="NumPressed(3)">
						</td>
						<td> </td>
						<td align="middle"> 
						<p align="left"> 
						<input name="btnMultiply" type="Button" value="  *  "
						          onClick="Operation(\'*\')">
						</p>
						</td>
						<td align="middle"> 
						<input name="btnDivide" type="Button" value="  /  "
						          onClick="Operation(\'/\')">
						</td>
						</tr>
						<tr> 
						<td> 
						<input name="btnZero" type="Button" value="  0  " onClick="NumPressed(0)">
						</td>
						<td> 
						<input name="btnDecimal" type="Button" value="  .  " onClick="Decimal()">
						</td>
						<td colspan="3"> </td>
						<td> 
						<input name="btnEquals" type="Button" value="  =  " onClick="Operation(\'=\')">
						</td>
						</tr>
						</table>
						</blockquote>
						</div>
						</form>
						<script LANGUAGE="JavaScript">
						// Courtesy of SimplytheBest.net - http://simplythebest.net/scripts/
						var FKeyPad = document.Keypad;
						var Accum = 0;
						var FlagNewNum = false;
						var PendingOp = "";
						function NumPressed (Num) {
						if (FlagNewNum) {
						FKeyPad.ReadOut.value = Num;
						FlagNewNum = false;
						}
						else {
						if (FKeyPad.ReadOut.value == "0")
						FKeyPad.ReadOut.value = Num;
						else
						FKeyPad.ReadOut.value += Num;
						}
						}
						function Operation (Op) {
						var Readout = FKeyPad.ReadOut.value;
						if (FlagNewNum && PendingOp != "=");
						else
						{
						FlagNewNum = true;
						if ( \'+\' == PendingOp )
						Accum += parseFloat(Readout);
						else if ( \'-\' == PendingOp )
						Accum -= parseFloat(Readout);
						else if ( \'/\' == PendingOp )
						Accum /= parseFloat(Readout);
						else if ( \'*\' == PendingOp )
						Accum *= parseFloat(Readout);
						else
						Accum = parseFloat(Readout);
						FKeyPad.ReadOut.value = Accum;
						PendingOp = Op;
						}
						}
						function Decimal () {
						var curReadOut = FKeyPad.ReadOut.value;
						if (FlagNewNum) {
						curReadOut = "0.";
						FlagNewNum = false;
						}
						else
						{
						if (curReadOut.indexOf(".") == -1)
						curReadOut += ".";
						}
						FKeyPad.ReadOut.value = curReadOut;
						}
						function ClearEntry () {
						FKeyPad.ReadOut.value = "0";
						FlagNewNum = true;
						}
						function Clear () {
						Accum = 0;
						PendingOp = "";
						ClearEntry();
						}
						function Neg () {
						FKeyPad.ReadOut.value = parseFloat(FKeyPad.ReadOut.value) * -1;
						}
						function Percent () {
						FKeyPad.ReadOut.value = (parseFloat(FKeyPad.ReadOut.value) / 100) * parseFloat(Accum);
						}
						</script>
						<!-- Calculator End -->';*/
        //$str_calc = '<p>Hello!</p>';
        
        
        // detect all the <script> zones to take out
        $excludes = array();
        preg_match_all('/<script language(.+?)<\/script>/is', $text, $listofexcludes);

        // take out all the <script> zones from text
        foreach (array_unique($listofexcludes[0]) as $key => $value) {
            $excludes['<+'.$key.'+>'] = $value;
        }
        if ($excludes) {
            $text = str_replace($excludes, array_keys($excludes), $text);
        }

        // this is the meat of the code - this is run every time
        //$text = str_replace('$$calc$$', 'Boo!', $text);

        $text = str_replace('$$calc$$', $CFG->filter_calculator_code, $text);
        
        // Recover all the <script> zones to text
        if ($excludes) {
            $text = str_replace(array_keys($excludes), $excludes, $text);
        }
    }
}
